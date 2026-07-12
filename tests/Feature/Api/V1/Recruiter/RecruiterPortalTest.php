<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Recruiter;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\Trade;
use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecruiterPortalTest extends TestCase
{
    private static bool $bootstrapped = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$bootstrapped) {
            if (! Schema::hasTable('recruiter_organizations')) {
                $this->artisan('migrate', [
                    '--path' => 'database/migrations/2026_06_05_100000_create_recruiter_portal_tables.php',
                    '--force' => true,
                ]);
            }
            if (! Schema::hasTable('recruiter_onboarding_applications')) {
                $this->artisan('migrate', [
                    '--path' => 'database/migrations/2026_06_08_100000_create_recruiter_onboarding_and_offers_tables.php',
                    '--force' => true,
                ]);
            }
            if (Schema::hasTable('permissions') && Role::query()->count() === 0) {
                $this->seed(PermissionSeeder::class);
                $this->seed(RoleSeeder::class);
            }
            self::$bootstrapped = true;
        }

        if (! Schema::hasTable('recruiter_organizations') || ! Schema::hasTable('roles')) {
            $this->markTestSkipped('Base de données de test incomplète — exécutez `php artisan migrate` puis les seeders.');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function staff_can_assign_approved_profile_to_recruiter_with_visible_sections(): void
    {
        [$recruiter, $organization] = $this->makeRecruiterWithOrganization();
        $candidate = $this->makeApprovedCandidate();

        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        Sanctum::actingAs($staff);

        $this->postJson('/api/v1/identity/admin/recruiter-assignments', [
            'recruiter_organization_id' => $organization->id,
            'candidate_user_id' => $candidate->id,
            'note' => 'Profil partagé',
            'visible_sections' => ['profile', 'contact', 'experiences'],
        ])->assertStatus(201)
            ->assertJsonPath('data.assignment.visible_sections', ['profile', 'contact', 'experiences']);

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/v1/recruiter/assignments/{$candidate->id}")
            ->assertOk()
            ->assertJsonPath('data.candidate.id', $candidate->id)
            ->assertJsonPath('data.visible_sections', ['profile', 'contact', 'experiences'])
            ->assertJsonMissingPath('data.candidate.educations');
    }

    #[Test]
    public function staff_can_mask_contact_fields_when_sharing_profile(): void
    {
        [$recruiter, $organization] = $this->makeRecruiterWithOrganization();
        $candidate = $this->makeApprovedCandidate();
        $candidate->update(['email' => 'marie-'.uniqid().'@example.com', 'phone_number1' => '+2376'.random_int(10000000, 99999999)]);

        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        Sanctum::actingAs($staff);

        $this->postJson('/api/v1/identity/admin/recruiter-assignments', [
            'recruiter_organization_id' => $organization->id,
            'candidate_user_id' => $candidate->id,
            'visible_sections' => ['profile', 'professional', 'experiences'],
            'masked_fields' => ['contact_email', 'contact_phone', 'profile_address'],
        ])->assertStatus(201);

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/v1/recruiter/assignments/{$candidate->id}")
            ->assertOk()
            ->assertJsonMissingPath('data.candidate.email')
            ->assertJsonMissingPath('data.candidate.phone_number1')
            ->assertJsonPath('data.masked_fields', ['contact_email', 'contact_phone', 'profile_address']);
    }

    #[Test]
    public function recruiter_cannot_view_unassigned_candidate(): void
    {
        [$recruiter] = $this->makeRecruiterWithOrganization();
        $candidate = $this->makeApprovedCandidate();

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/v1/recruiter/assignments/{$candidate->id}")
            ->assertForbidden();
    }

    #[Test]
    public function staff_can_bulk_assign_candidates_matching_profile_search_filters(): void
    {
        [$recruiter, $organization] = $this->makeRecruiterWithOrganization();

        $category = Category::query()->create([
            'name' => ['fr' => 'Santé bulk', 'en' => 'Health bulk'],
            'slug' => 'sante-bulk-'.uniqid(),
            'description' => ['fr' => 'Santé', 'en' => 'Health'],
        ]);
        $trade = Trade::query()->create([
            'category_id' => $category->id,
            'name' => ['fr' => 'Infirmier', 'en' => 'Nurse'],
            'slug' => 'infirmier-bulk-'.uniqid(),
            'is_active' => true,
        ]);

        $matching = $this->makeApprovedCandidate();
        $matching->trades()->attach($trade->id, ['years_of_experience' => 3]);

        $other = $this->makeApprovedCandidate();

        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        Sanctum::actingAs($staff);

        $this->postJson('/api/v1/identity/admin/recruiter-assignments/bulk', [
            'recruiter_organization_id' => $organization->id,
            'note' => 'Lot infirmiers',
            'visible_sections' => ['profile', 'contact'],
            'filters' => [
                'role' => ApplicationRole::CANDIDATE,
                'trade_ids' => [(string) $trade->id],
            ],
            'only_approved' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.bulk.assigned_count', 1)
            ->assertJsonPath('data.matched_count', 1);

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/v1/recruiter/assignments/{$matching->id}")
            ->assertOk();

        $this->getJson("/api/v1/recruiter/assignments/{$other->id}")
            ->assertForbidden();
    }

    #[Test]
    public function provisioning_activates_organization_without_custom_domains(): void
    {
        Http::fake();

        $slug = 'acme-'.uniqid();

        $organization = RecruiterOrganization::query()->create([
            'name' => 'ACME Recrutement',
            'slug' => $slug,
            'status' => RecruiterOrganizationStatus::Pending,
            'portal_host' => "{$slug}.recruteur.jbis.cm",
            'api_host' => "api.{$slug}.recruteur.jbis.cm",
            'settings' => [],
        ]);

        $job = new \App\Core\Domain\Recruiter\Jobs\ProvisionRecruiterInfrastructureJob($organization->id);
        $job->handle(app(\App\Core\Domain\Recruiter\Services\RecruiterInfrastructureProvisioner::class));

        $organization->refresh();
        $this->assertSame(RecruiterOrganizationStatus::Active, $organization->status);
        $this->assertNotNull($organization->provisioned_at);
        $this->assertNull($organization->portal_host);
        $this->assertNull($organization->api_host);
        Http::assertNothingSent();
    }

    #[Test]
    public function public_can_submit_recruiter_onboarding_application(): void
    {
        Mail::fake();
        config()->set('services.recruiter.onboarding_enabled', true);

        $email = 'onboard-'.uniqid().'@example.com';

        $response = $this->postJson('/api/v1/public/recruiter-onboarding', [
            'company_name' => 'ACME SARL',
            'contact_name' => 'Paul Recruteur',
            'contact_email' => $email,
            'contact_phone' => '+237600000000',
            'activity_description' => 'Cabinet de recrutement',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.application.status', 'submitted')
            ->assertJsonPath('data.application.company_name', 'ACME SARL');

        $this->assertDatabaseHas('recruiter_onboarding_applications', [
            'contact_email' => $email,
            'status' => 'submitted',
        ]);
    }

    #[Test]
    public function staff_can_approve_onboarding_and_provision_recruiter_portal(): void
    {
        Mail::fake();

        $email = 'approve-'.uniqid().'@example.com';

        $applicationId = (int) $this->postJson('/api/v1/public/recruiter-onboarding', [
            'company_name' => 'Beta HR',
            'contact_name' => 'Sophie Beta',
            'contact_email' => $email,
            'desired_slug' => 'beta-'.uniqid(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->json('data.application.id');

        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        Sanctum::actingAs($staff);

        $this->patchJson("/api/v1/identity/admin/recruiter-onboarding/{$applicationId}/review", [
            'decision' => 'approve',
        ])->assertOk()
            ->assertJsonPath('data.application.status', 'approved');

        $this->assertDatabaseHas('recruiter_organizations', [
            'name' => 'Beta HR',
        ]);

        Mail::assertSent(\App\Core\Application\Mail\Mailable\RecruiterPortalApprovedMail::class);
    }

    #[Test]
    public function recruiter_can_submit_offer_and_staff_can_publish(): void
    {
        [$recruiter, $organization] = $this->makeRecruiterWithOrganization();
        Sanctum::actingAs($recruiter);

        $category = Category::query()->create([
            'name' => ['fr' => 'Informatique', 'en' => 'IT'],
            'slug' => 'it-recruiter-test',
            'description' => ['fr' => 'IT', 'en' => 'IT'],
        ]);
        $trade = Trade::query()->create([
            'category_id' => $category->id,
            'name' => ['fr' => 'Développeur Laravel', 'en' => 'Laravel Developer'],
            'slug' => 'laravel-developer-test',
            'is_active' => true,
        ]);

        $offerSubmissionId = (int) $this->postJson('/api/v1/recruiter/offers', [
            'trade_id' => $trade->id,
            'description' => ['fr' => 'Mission longue durée'],
        ])->json('data.submission.id');

        $this->postJson("/api/v1/recruiter/offers/{$offerSubmissionId}/submit")
            ->assertOk()
            ->assertJsonPath('data.submission.status', 'submitted');

        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        Sanctum::actingAs($staff);

        $this->patchJson("/api/v1/identity/admin/recruiter-offers/{$offerSubmissionId}/review", [
            'decision' => 'approve',
        ])->assertOk()
            ->assertJsonPath('data.submission.status', 'approved');

        $this->assertDatabaseHas('recruiter_offer_submissions', [
            'id' => $offerSubmissionId,
            'status' => 'approved',
            'recruiter_organization_id' => $organization->id,
        ]);

        $offerId = (int) $this->getJson("/api/v1/identity/admin/recruiter-offers/{$offerSubmissionId}")
            ->json('data.submission.offer_id');

        $this->assertGreaterThan(0, $offerId);
        $this->assertDatabaseHas('offers', ['id' => $offerId, 'status' => 'PUBLISHED']);
    }

    #[Test]
    public function recruiter_can_submit_profile_request_and_staff_can_transmit_matches(): void
    {
        if (! Schema::hasTable('recruiter_profile_requests')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_07_06_100000_create_recruiter_profile_requests_table.php',
                '--force' => true,
            ]);
        }
        if (! Schema::hasColumn('recruiter_profile_assignments', 'recruiter_profile_request_id')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_07_06_100001_add_recruiter_profile_request_id_to_assignments.php',
                '--force' => true,
            ]);
        }

        [$recruiter, $organization] = $this->makeRecruiterWithOrganization();
        $candidate = $this->makeApprovedCandidate();

        $category = Category::query()->create([
            'name' => ['fr' => 'BTP', 'en' => 'Construction'],
            'slug' => 'btp-recruiter-test-'.uniqid(),
            'description' => ['fr' => 'BTP', 'en' => 'Construction'],
        ]);
        $trade = Trade::query()->create([
            'category_id' => $category->id,
            'name' => ['fr' => 'Soudeur', 'en' => 'Welder'],
            'slug' => 'soudeur-test-'.uniqid(),
            'is_active' => true,
        ]);
        $candidate->trades()->attach($trade->id, ['years_of_experience' => 5]);

        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $recruiter->givePermissionTo([
            'recruiterprofilerequest.view',
            'recruiterprofilerequest.create',
            'recruiterprofilerequest.update',
        ]);

        Sanctum::actingAs($recruiter);

        $requestId = (int) $this->postJson('/api/v1/recruiter/profile-requests', [
            'title' => 'Soudeurs Canada',
            'trade_ids' => [$trade->id],
            'quantity_needed' => 5,
            'min_years_experience' => 2,
        ])->assertCreated()
            ->json('data.request.id');

        $this->postJson("/api/v1/recruiter/profile-requests/{$requestId}/submit")
            ->assertOk()
            ->assertJsonPath('data.request.status', 'matched')
            ->assertJsonPath('data.request.matched_count', 1);

        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        $staff->givePermissionTo([
            'recruiterprofilerequest.view',
            'recruiterprofilerequest.update',
            'recruiterassignment.create',
        ]);
        Sanctum::actingAs($staff);

        $this->getJson("/api/v1/identity/admin/recruiter-profile-requests/{$requestId}")
            ->assertOk()
            ->assertJsonPath('data.request.matched_count', 1);

        $this->postJson("/api/v1/identity/admin/recruiter-profile-requests/{$requestId}/transmit", [
            'candidate_user_ids' => [$candidate->id],
            'masked_fields' => ['contact_email', 'contact_phone'],
        ])->assertOk()
            ->assertJsonPath('data.request.status', 'transmitted')
            ->assertJsonPath('data.bulk.assigned_count', 1);

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/v1/recruiter/assignments/{$candidate->id}")
            ->assertOk()
            ->assertJsonPath('data.candidate.id', $candidate->id);
    }

    #[Test]
    public function cpanel_mailbox_routes_require_admin_access(): void
    {
        $this->assertTrue(Role::query()->where('name', ApplicationRole::CANDIDATE)->exists());

        $user = User::factory()->create();
        $user->assignRole(ApplicationRole::CANDIDATE);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/cpanel/mailboxes')->assertForbidden();
    }

    /**
     * @return array{0: User, 1: RecruiterOrganization}
     */
    private function makeRecruiterWithOrganization(): array
    {
        $slug = 'test-org-'.uniqid();

        $organization = RecruiterOrganization::query()->create([
            'name' => 'Test Org',
            'slug' => $slug,
            'status' => RecruiterOrganizationStatus::Active,
            'portal_host' => "{$slug}.recruteur.jbis.cm",
            'api_host' => "api.{$slug}.recruteur.jbis.cm",
            'settings' => [],
        ]);

        $recruiter = User::factory()->create();
        $recruiter->assignRole(ApplicationRole::RECRUITER);
        $organization->members()->attach($recruiter->id, ['is_owner' => true]);

        return [$recruiter, $organization];
    }

    private function makeApprovedCandidate(): User
    {
        $candidate = User::factory()->create();
        $candidate->assignRole(ApplicationRole::CANDIDATE);

        UserProfile::query()->create([
            'user_id' => $candidate->id,
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'is_approved' => true,
        ]);

        return $candidate->fresh(['profile']);
    }
}
