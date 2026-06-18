<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Catalog;

use App\Core\Domain\Catalog\Models\Benefit;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\SqliteTestCase;

class AdminCatalogControllerTest extends SqliteTestCase
{
    use RefreshDatabase;

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
    public function candidate_cannot_access_admin_referentials(): void
    {
        $candidate = User::factory()->create();
        $candidate->assignRole(ApplicationRole::CANDIDATE);
        Sanctum::actingAs($candidate);

        $this->getJson('/api/v1/catalog/admin/referentials')
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_list_resources(): void
    {
        Sanctum::actingAs($this->adminUser());

        $this->getJson('/api/v1/catalog/admin/referentials')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'resources' => [
                        ['key', 'label'],
                    ],
                ],
            ])
            ->assertJsonFragment(['key' => 'benefits']);
    }

    #[Test]
    public function admin_can_create_update_and_delete_benefit(): void
    {
        Sanctum::actingAs($this->adminUser());

        $slug = 'test-benefit-'.Str::lower(Str::random(6));

        $create = $this->postJson('/api/v1/catalog/admin/referentials/benefits', [
            'name' => ['fr' => 'Test avantage', 'en' => 'Test benefit'],
            'slug' => $slug,
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.item.slug', $slug);

        $id = (int) $create->json('data.item.id');
        $this->assertDatabaseHas('benefits', ['id' => $id, 'slug' => $slug]);

        $this->putJson("/api/v1/catalog/admin/referentials/benefits/{$id}", [
            'name' => ['fr' => 'Test avantage modifié', 'en' => 'Updated test benefit'],
        ])
            ->assertOk()
            ->assertJsonPath('data.item.name.fr', 'Test avantage modifié');

        $this->deleteJson("/api/v1/catalog/admin/referentials/benefits/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('benefits', ['id' => $id]);
    }

    #[Test]
    public function admin_can_search_benefits(): void
    {
        Sanctum::actingAs($this->adminUser());

        Benefit::query()->create([
            'name' => ['fr' => 'Logement fourni', 'en' => 'Housing'],
            'slug' => 'housing-'.Str::lower(Str::random(4)),
        ]);
        Benefit::query()->create([
            'name' => ['fr' => 'Autre chose', 'en' => 'Other'],
            'slug' => 'other-'.Str::lower(Str::random(4)),
        ]);

        $response = $this->getJson('/api/v1/catalog/admin/referentials/benefits?search=Logement');

        $response->assertOk()
            ->assertJsonPath('data.meta.total', 1);

        $this->assertStringContainsString('housing', (string) $response->json('data.items.0.slug'));
    }

    #[Test]
    public function admin_create_validates_required_translatable_name(): void
    {
        Sanctum::actingAs($this->adminUser());

        $this->postJson('/api/v1/catalog/admin/referentials/benefits', [
            'slug' => 'invalid-benefit',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function admin_returns_not_found_for_unknown_resource(): void
    {
        Sanctum::actingAs($this->adminUser());

        $this->getJson('/api/v1/catalog/admin/referentials/unknown-resource')
            ->assertNotFound();
    }

    #[Test]
    public function admin_can_manage_trade_with_category_relation(): void
    {
        Sanctum::actingAs($this->adminUser());

        $category = Category::query()->create([
            'name' => ['fr' => 'Santé', 'en' => 'Health'],
            'slug' => 'health-'.Str::lower(Str::random(4)),
            'is_active' => true,
        ]);

        $create = $this->postJson('/api/v1/catalog/admin/referentials/trades', [
            'category_id' => $category->id,
            'name' => ['fr' => 'Infirmier', 'en' => 'Nurse'],
            'is_active' => true,
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.item.category_id', $category->id)
            ->assertJsonPath('data.item.slug', 'nurse');

        $id = (int) $create->json('data.item.id');

        $this->getJson("/api/v1/catalog/admin/referentials/trades/{$id}")
            ->assertOk()
            ->assertJsonPath('data.item.id', $id);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);

        return $admin;
    }
}
