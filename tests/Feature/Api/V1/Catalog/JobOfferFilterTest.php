<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Catalog;

use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\ContractType;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Trade;
use App\Core\Domain\Catalog\States\OfferStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobOfferFilterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_filters_offers_by_search_term_in_french(): void
    {
        $category = Category::query()->create([
            'name' => ['fr' => 'Informatique', 'en' => 'IT'],
            'slug' => 'it-'.Str::lower(Str::random(6)),
            'description' => ['fr' => 'IT', 'en' => 'IT'],
        ]);

        $trade = Trade::query()->create([
            'category_id' => $category->id,
            'name' => ['fr' => 'Expert Laravel', 'en' => 'Laravel Expert'],
            'slug' => 'expert-laravel-'.Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        Offer::factory()->create([
            'trade_id' => $trade->id,
            'description' => ['fr' => 'Poste expert sur des projets Laravel.', 'en' => 'Expert Laravel role.'],
            'status' => OfferStatus::Published,
        ]);

        $response = $this->withHeaders(['X-Locale' => 'fr'])
            ->getJson('/api/v1/public/offers?filter[search]=Expert');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.offers');

        $title = $response->json('data.offers.0.title');
        $titleFr = is_array($title) ? (string) ($title['fr'] ?? '') : (string) $title;
        $this->assertStringContainsString('Expert Laravel', $titleFr);
    }

    #[Test]
    public function it_filters_by_multiple_contract_types(): void
    {
        $cdi = ContractType::query()->create([
            'name' => ['fr' => 'CDI', 'en' => 'Full-time'],
            'slug' => 'cdi-'.Str::lower(Str::random(8)),
            'color_code' => '#00ff88',
        ]);
        $stage = ContractType::query()->create([
            'name' => ['fr' => 'Stage', 'en' => 'Internship'],
            'slug' => 'stage-'.Str::lower(Str::random(8)),
            'color_code' => '#ff00ff',
        ]);
        $freelance = ContractType::query()->create([
            'name' => ['fr' => 'Freelance', 'en' => 'Freelance'],
            'slug' => 'freelance-'.Str::lower(Str::random(8)),
            'color_code' => '#d4af37',
        ]);

        Offer::factory()->create(['contract_type_id' => $cdi->id]);
        Offer::factory()->create(['contract_type_id' => $stage->id]);
        Offer::factory()->create(['contract_type_id' => $freelance->id]);

        $response = $this->withHeaders(['X-Locale' => 'fr'])
            ->getJson('/api/v1/public/offers?filter[contract_type]=CDI,Stage');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.offers');
    }

    #[Test]
    public function it_strictly_excludes_non_public_offers(): void
    {
        Offer::factory()->draft()->create();
        Offer::factory()->expired()->create();
        Offer::factory()->create();

        $response = $this->getJson('/api/v1/public/offers');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.offers');
    }

    #[Test]
    public function it_resolves_offer_by_slug_with_mixed_case_suffix(): void
    {
        $slug = 'developpeur-full-stack-YQiX4';

        Offer::factory()->create([
            'slug' => ['fr' => $slug, 'en' => $slug],
            'status' => OfferStatus::Published,
            'published_at' => now()->subDay(),
            'expiration_date' => now()->addMonth(),
        ]);

        $this->getJson("/api/v1/public/offers/{$slug}")
            ->assertOk()
            ->assertJsonPath('data.offer.slug.fr', $slug);
    }

    #[Test]
    public function it_returns_not_found_for_unknown_public_offer_slug(): void
    {
        $this->getJson('/api/v1/public/offers/inconnue-abc12')
            ->assertNotFound();
    }
}
