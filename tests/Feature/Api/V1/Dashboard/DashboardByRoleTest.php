<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Dashboard;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardByRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Role::query()->count() === 0) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RoleSeeder::class);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function admin_receives_admin_variant_with_stats(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.dashboard.variant', 'admin')
            ->assertJsonStructure([
                'data' => [
                    'dashboard' => [
                        'variant',
                        'stats' => [
                            'candidates_total',
                            'applications_pending',
                            'applications_total',
                        ],
                        'my_activity',
                    ],
                ],
            ]);
    }

    #[Test]
    public function staff_receives_staff_variant_with_stats(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        Sanctum::actingAs($staff);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.dashboard.variant', 'staff')
            ->assertJsonStructure([
                'data' => [
                    'dashboard' => [
                        'variant',
                        'stats' => [
                            'applications_pending',
                            'applications_in_progress',
                            'my_actions_today',
                        ],
                        'my_activity',
                    ],
                ],
            ]);
    }

    #[Test]
    public function candidate_receives_candidate_variant_with_profile_completion(): void
    {
        $candidate = User::factory()->create();
        $candidate->assignRole(ApplicationRole::CANDIDATE);
        Sanctum::actingAs($candidate);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.dashboard.variant', 'candidate')
            ->assertJsonStructure([
                'data' => [
                    'dashboard' => [
                        'variant',
                        'profile_completion' => ['overall_percent', 'sections', 'counts'],
                        'applications_summary',
                        'active_application',
                    ],
                ],
            ]);
    }

    #[Test]
    public function recruiter_receives_recruiter_variant_with_organization(): void
    {
        if (! Schema::hasTable('recruiter_organizations')) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_06_05_100000_create_recruiter_portal_tables.php',
                '--force' => true,
            ]);
        }

        [$recruiter, $organization] = $this->makeRecruiterWithOrganization();
        Sanctum::actingAs($recruiter);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.dashboard.variant', 'recruiter')
            ->assertJsonPath('data.dashboard.organization.id', $organization->id)
            ->assertJsonStructure([
                'data' => [
                    'dashboard' => [
                        'variant',
                        'organization' => ['id', 'name', 'status'],
                        'stats' => [
                            'assignments_active',
                            'offers_pending',
                            'offers_total',
                        ],
                        'recent_assignments',
                    ],
                ],
            ]);
    }

    #[Test]
    public function partner_without_dashboard_role_is_forbidden(): void
    {
        if (! Role::query()->where('name', ApplicationRole::PARTNER)->exists()) {
            $this->markTestSkipped('Rôle partner non seedé.');
        }

        $partner = User::factory()->create();
        $partner->assignRole(ApplicationRole::PARTNER);
        Sanctum::actingAs($partner);

        $this->getJson('/api/v1/dashboard')->assertForbidden();
    }

    /**
     * @return array{0: User, 1: RecruiterOrganization}
     */
    private function makeRecruiterWithOrganization(): array
    {
        $slug = 'dash-org-'.uniqid();

        $organization = RecruiterOrganization::query()->create([
            'name' => 'Dashboard Test Org',
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
