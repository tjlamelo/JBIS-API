<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Catalog;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\States\OfferStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobOfferFilterTest extends TestCase
{
    use RefreshDatabase;

#[Test]
    public function it_filters_offers_by_search_term_in_french(): void
    {
        Offer::factory()->create([
            'title' => ['fr' => 'Expert Laravel', 'en' => 'Laravel Expert'],
            'status' => OfferStatus::Published,
        ]);

        $response = $this->withHeaders(['X-Locale' => 'fr'])
            ->getJson('/api/v1/public/offers?filter[search]=Expert');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.offers');
        
        // On vérifie que le titre contient bien le mot cherché
        $this->assertStringContainsString('Expert Laravel', $response->json('data.offers.0.title'));
    }

    #[Test]
    public function it_filters_by_multiple_contract_types(): void
    {
        // On s'assure que les contrats sont bien dans la clé JSON attendue
        Offer::factory()->create(['contract_type' => ['fr' => 'CDI']]);
        Offer::factory()->create(['contract_type' => ['fr' => 'Stage']]);
        Offer::factory()->create(['contract_type' => ['fr' => 'Freelance']]);

        // Ce test passera si tu as mis le callback dans Job OfferIndexQuery
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
        Offer::factory()->create(); // Published & Active           

        $response = $this->getJson('/api/v1/public/offers');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data.offers');
    }
}