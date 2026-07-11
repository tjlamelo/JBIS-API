<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Partner;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission as P;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Partner\Actions\CreatePartnerOrganizationAction;
use App\Core\Domain\Partner\Enums\PartnerCohortStatus;
use App\Core\Domain\Partner\Models\PartnerCohort;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class PartnerPortalTest extends TestCase
{
    private static bool $bootstrapped = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$bootstrapped) {
            if (! Schema::hasTable('partner_organizations')) {
                $this->artisan('migrate', [
                    '--path' => 'database/migrations/2026_07_06_160000_create_partner_portal_tables.php',
                    '--force' => true,
                ]);
            }
            if (Schema::hasTable('permissions') && Role::query()->count() === 0) {
                $this->seed(PermissionSeeder::class);
                $this->seed(RoleSeeder::class);
            }
            self::$bootstrapped = true;
        }

        if (! Schema::hasTable('partner_organizations')) {
            $this->markTestSkipped('Migration partner manquante.');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function partner_can_create_cohort_add_student_and_submit(): void
    {
        [$partner, $organization] = $this->makePartnerWithOrganization();
        Sanctum::actingAs($partner);

        $create = $this->postJson('/api/v1/partner/cohorts', [
            'name' => 'L3 Informatique 2026',
            'academic_year' => '2025-2026',
            'field_of_study' => 'Informatique',
            'expected_student_count' => 2,
        ]);

        $create->assertStatus(201)
            ->assertJsonPath('data.cohort.status', 'draft');

        $cohortId = (int) $create->json('data.cohort.id');

        $this->postJson("/api/v1/partner/cohorts/{$cohortId}/students", [
            'invited_name' => 'Jean Dupont',
            'invited_email' => 'jean.dupont.'.uniqid().'@example.com',
        ])->assertStatus(201);

        $submit = $this->postJson("/api/v1/partner/cohorts/{$cohortId}/submit");
        $submit->assertOk()
            ->assertJsonPath('data.cohort.status', 'submitted');
    }

    #[Test]
    public function staff_can_review_submitted_cohort(): void
    {
        [$partner, $organization] = $this->makePartnerWithOrganization();

        $cohort = PartnerCohort::query()->create([
            'partner_organization_id' => $organization->id,
            'name' => 'Promotion test',
            'status' => PartnerCohortStatus::Submitted,
            'submitted_at' => now(),
            'submitted_by_user_id' => $partner->id,
            'expected_student_count' => 1,
        ]);

        $staff = User::factory()->create();
        $staff->assignRole(ApplicationRole::STAFF);
        foreach ([
            P::name('partnercohort', P::VIEW),
            P::name('partnercohort', P::UPDATE),
        ] as $permission) {
            $staff->givePermissionTo($permission);
        }

        Sanctum::actingAs($staff);

        $this->patchJson("/api/v1/identity/admin/partner-cohorts/{$cohort->id}/review", [
            'decision' => 'approve',
            'staff_note' => 'Dossier conforme',
        ])->assertOk()
            ->assertJsonPath('data.cohort.status', 'active');
    }

    /**
     * @return array{0: User, 1: \App\Core\Domain\Partner\Models\PartnerOrganization}
     */
    private function makePartnerWithOrganization(): array
    {
        $partner = User::factory()->create();
        $partner->assignRole(ApplicationRole::PARTNER);
        foreach ([
            P::name('partnerorganization', P::VIEW),
            P::name('partnercohort', P::VIEW),
            P::name('partnercohort', P::CREATE),
            P::name('partnercohort', P::UPDATE),
            P::name('partnercohortstudent', P::VIEW),
            P::name('partnercohortstudent', P::CREATE),
            P::name('partnercohortstudent', P::UPDATE),
        ] as $permission) {
            $partner->givePermissionTo($permission);
        }

        $organization = app(CreatePartnerOrganizationAction::class)->execute([
            'name' => 'Ecole Test '.uniqid(),
            'owner_user_id' => $partner->id,
        ]);

        return [$partner, $organization];
    }
}
