<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Recruiter;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
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
    public function recruiter_can_create_and_list_submissions(): void
    {
        [$recruiter, $organization] = $this->makeRecruiterWithOrganization();
        Sanctum::actingAs($recruiter);

        $create = $this->postJson('/api/v1/recruiter/submissions', [
            'email' => 'candidat-recruteur-'.uniqid().'@example.com',
            'name' => 'Jean Dupont',
        ]);

        $create->assertStatus(201)
            ->assertJsonPath('data.submission.status', 'draft');

        $submissionId = (int) $create->json('data.submission.id');

        $list = $this->getJson('/api/v1/recruiter/submissions');
        $list->assertOk();
        $this->assertGreaterThanOrEqual(1, (int) $list->json('data.meta.total'));

        $this->assertDatabaseHas('recruiter_profile_submissions', [
            'id' => $submissionId,
            'recruiter_organization_id' => $organization->id,
        ]);
    }

    #[Test]
    public function staff_can_review_submission_and_assign_approved_profile(): void
    {
        [$recruiter, $organization] = $this->makeRecruiterWithOrganization();
        Sanctum::actingAs($recruiter);

        $submissionId = (int) $this->postJson('/api/v1/recruiter/submissions', [
            'email' => 'assign-test-'.uniqid().'@example.com',
            'name' => 'Marie Martin',
        ])->json('data.submission.id');

        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        Sanctum::actingAs($staff);

        $this->patchJson("/api/v1/identity/admin/recruiter-submissions/{$submissionId}/review", [
            'decision' => 'approve',
        ])->assertOk()
            ->assertJsonPath('data.submission.status', 'approved');

        $candidateId = (int) $this->getJson("/api/v1/identity/admin/recruiter-submissions/{$submissionId}")
            ->json('data.submission.candidate.id');

        $this->postJson('/api/v1/identity/admin/recruiter-assignments', [
            'recruiter_organization_id' => $organization->id,
            'candidate_user_id' => $candidateId,
            'note' => 'Profil validé',
        ])->assertStatus(201);

        Sanctum::actingAs($recruiter);

        $this->getJson("/api/v1/recruiter/assignments/{$candidateId}")
            ->assertOk()
            ->assertJsonPath('data.candidate.id', $candidateId);
    }

    #[Test]
    public function provisioning_job_creates_subdomains_via_cpanel_fake(): void
    {
        config()->set('services.cpanel', [
            'host' => 'light.o2switch.net',
            'username' => 'cpanel-user',
            'token' => 'cpanel-token',
            'primary_domain' => 'jbis.cm',
            'recruiter_base_domain' => 'jbis.cm',
            'recruiter_portal_prefix' => 'recruteur',
            'recruiter_docroot' => '/home/user/public_html',
            'timeout' => 10,
        ]);

        Http::fake([
            'https://light.o2switch.net:2083/execute/SubDomain/addsubdomain*' => Http::response(['status' => 1], 200),
            'https://light.o2switch.net:2083/execute/Email/add_pop*' => Http::response(['status' => 1], 200),
        ]);

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
        config()->set('services.recruiter.auto_provision_on_approval', false);

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

        $offerSubmissionId = (int) $this->postJson('/api/v1/recruiter/offers', [
            'title' => ['fr' => 'Développeur Laravel'],
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
}
