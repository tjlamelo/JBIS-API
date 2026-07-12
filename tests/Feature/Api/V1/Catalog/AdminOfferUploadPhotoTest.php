<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Catalog;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Shared\Media\Actions\StoreMediaAction;
use App\Core\Domain\Shared\Media\DTOs\UploadedMediaDto;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class AdminOfferUploadPhotoTest extends TestCase
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
    public function admin_can_upload_offer_photo_with_photo_field_only(): void
    {
        $this->mock(StoreMediaAction::class, function ($mock): void {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturn(new UploadedMediaDto(
                    fileName: 'flyer.jpg',
                    localOptimizedPath: 'catalog/offers/flyers/flyer.jpg',
                    localRawPath: 'catalog/offers/flyers/raw/flyer.jpg',
                    cloudinaryId: null,
                    publicUrl: 'https://cdn.example.com/flyer.jpg',
                ));
        });

        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $response = $this->post(
            '/api/v1/catalog/admin/offers/upload-photo',
            [
                'photo' => UploadedFile::fake()->image('flyer.jpg', 400, 300),
            ],
            ['Accept' => 'application/json'],
        );

        $response->assertOk()
            ->assertJsonPath('data.message', __('Photo telechargee avec succes.'));
    }

    #[Test]
    public function upload_without_file_returns_validation_error(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/catalog/admin/offers/upload-photo', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }
}
