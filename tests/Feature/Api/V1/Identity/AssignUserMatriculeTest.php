<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Identity;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class AssignUserMatriculeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('permissions') && Role::query()->count() === 0) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RoleSeeder::class);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function admin_can_assign_and_force_regenerate_matricule(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $candidate = User::factory()->create();
        UserProfile::query()->create([
            'user_id' => $candidate->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $assign = $this->postJson("/api/v1/identity/admin/users/{$candidate->id}/matricule", [
            'service' => 'placement_international',
            'custom_tag' => 'yde',
        ])->assertOk();

        $matricule = (string) $assign->json('data.assignment.matricule');
        $this->assertMatchesRegularExpression('/^JBIS-INTYDE-\d{4}-[A-Z0-9]{4}$/', $matricule);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $candidate->id,
            'matricule' => $matricule,
        ]);

        $this->postJson("/api/v1/identity/admin/users/{$candidate->id}/matricule", [
            'service' => 'candidat',
        ])->assertStatus(422);

        $regen = $this->postJson("/api/v1/identity/admin/users/{$candidate->id}/matricule", [
            'service' => 'candidat',
            'force' => true,
        ])->assertOk();

        $newMatricule = (string) $regen->json('data.assignment.matricule');
        $this->assertNotSame($matricule, $newMatricule);
        $this->assertTrue((bool) $regen->json('data.assignment.regenerated'));
    }
}
