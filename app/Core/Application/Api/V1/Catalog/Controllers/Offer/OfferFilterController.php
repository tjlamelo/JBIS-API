<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Location\Models\Country;
use App\Core\Infrastructure\Cache\AppCache;
use Illuminate\Http\JsonResponse;

class OfferFilterController
{
    public function __construct(
        private readonly AppCache $cache,
    ) {}

    public function __invoke(): JsonResponse
    {
        $locale = app()->getLocale();

        $filters = $this->cache->remember(
            $this->cache->catalogKey('offer_filters', $locale),
            3600,
            fn () => $this->buildFilters($locale),
        );

        return BaseResponse::ok($filters)->toJsonResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFilters(string $locale): array
    {
        return [
            'categories' => Category::where('is_active', true)
                ->get()
                ->map(fn ($c) => [
                    'label' => $c->name,
                    'value' => (string) $c->id,
                ])->values()->all(),

            'countries' => Country::where('is_active', true)
                ->get()
                ->map(fn ($c) => [
                    'label' => $c->name,
                    'value' => (string) $c->id,
                ])->values()->all(),

            'contract_types' => [
                ['label' => 'CDI', 'value' => 'CDI'],
                ['label' => 'CDD', 'value' => 'CDD'],
                ['label' => 'Stage', 'value' => 'Stage'],
                ['label' => 'Freelance', 'value' => 'Freelance'],
            ],

            'work_modes' => [
                ['label' => $locale === 'en' ? 'On-site' : 'Sur site', 'value' => 'on-site'],
                ['label' => $locale === 'en' ? 'Hybrid' : 'Hybride', 'value' => 'hybrid'],
                ['label' => $locale === 'en' ? 'Remote' : 'Télétravail', 'value' => 'remote'],
            ],

            'salary_steps' => [
                ['label' => $locale === 'en' ? 'No minimum' : 'Sans seuil', 'value' => ''],
                ['label' => '100 000 FCFA', 'value' => '100000'],
                ['label' => '200 000 FCFA', 'value' => '200000'],
                ['label' => '500 000 FCFA', 'value' => '500000'],
                ['label' => '1 000 000 FCFA', 'value' => '1000000'],
            ],
        ];
    }
}
