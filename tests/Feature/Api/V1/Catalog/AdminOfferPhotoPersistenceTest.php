<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Catalog;

use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Trade;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Location\Models\Country;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class AdminOfferPhotoPersistenceTest extends TestCase
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
    public function create_offer_persists_photo_media_from_photo_url(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $category = Category::query()->create([
            'name' => ['fr' => 'Tech', 'en' => 'Tech'],
            'slug' => 'tech-'.uniqid(),
        ]);
        $trade = Trade::query()->create([
            'category_id' => $category->id,
            'name' => ['fr' => 'Dev', 'en' => 'Dev'],
            'slug' => 'dev-'.uniqid(),
        ]);
        $country = Country::query()->firstOrCreate(
            ['code' => 'CM'],
            ['name' => ['fr' => 'Cameroun', 'en' => 'Cameroon']],
        );

        $photoUrl = 'https://cdn.example.com/offers/flyer-test.jpg';

        $response = $this->postJson('/api/v1/catalog/admin/offers', [
            'trade_id' => $trade->id,
            'country_id' => $country->id,
            'address' => 'Douala',
            'photo' => $photoUrl,
            'status' => 'DRAFT',
        ]);

        $response->assertCreated();

        $offerId = (int) $response->json('data.offer.id');
        $offer = Offer::query()->findOrFail($offerId);

        $this->assertIsArray($offer->photo_media);
        $this->assertSame($photoUrl, $offer->photo_media['public_url'] ?? null);
        $this->assertSame($photoUrl, $offer->photo['url'] ?? null);
    }

    #[Test]
    public function create_offer_persists_explicit_photo_media(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $category = Category::query()->create([
            'name' => ['fr' => 'Tech', 'en' => 'Tech'],
            'slug' => 'tech-'.uniqid(),
        ]);
        $trade = Trade::query()->create([
            'category_id' => $category->id,
            'name' => ['fr' => 'Dev', 'en' => 'Dev'],
            'slug' => 'dev-'.uniqid(),
        ]);
        $country = Country::query()->firstOrCreate(
            ['code' => 'CM'],
            ['name' => ['fr' => 'Cameroun', 'en' => 'Cameroon']],
        );

        $media = [
            'file_name' => 'flyer.jpg',
            'local_optimized_path' => 'catalog/offers/flyers/flyer.jpg',
            'local_raw_path' => 'catalog/offers/flyers/raw/flyer.jpg',
            'cloudinary_id' => null,
            'public_url' => 'https://cdn.example.com/flyer.jpg',
            'is_primary' => true,
        ];

        $response = $this->postJson('/api/v1/catalog/admin/offers', [
            'trade_id' => $trade->id,
            'country_id' => $country->id,
            'address' => 'Yaoundé',
            'photo' => $media['public_url'],
            'photo_media' => $media,
            'status' => 'DRAFT',
        ]);

        $response->assertCreated();

        $offerId = (int) $response->json('data.offer.id');
        $offer = Offer::query()->findOrFail($offerId);

        $this->assertSame($media['public_url'], $offer->photo_media['public_url'] ?? null);
        $this->assertSame($media['local_optimized_path'], $offer->photo_media['local_optimized_path'] ?? null);
    }
}
