<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Domain\Catalog\Models\OfferCategory;
use App\Core\Domain\Location\Models\Country;
use  App\Core\Application\Api\Responses\BaseResponse;
use Illuminate\Http\JsonResponse;

class OfferFilterController
{
    public function __invoke(): JsonResponse
    {
        $locale = app()->getLocale();

        $filters = [
            // Récupéré de la table offer_categories
            'categories' => OfferCategory::where('is_active', true)
                ->get()
                ->map(fn($c) => [
                    'label' => $c->name, 
                    'value' => (string) $c->id
                ])->values(),

            // Récupéré de la table countries
            'countries' => Country::where('is_active', true)
                ->get()
                ->map(fn($c) => [
                    'label' => $c->name,
                    'value' => (string) $c->id
                ])->values(),

            // Types de contrat (Peut être géré par un Enum ou une table dédiée)
            'contract_types' => [
                ['label' => 'CDI', 'value' => 'CDI'],
                ['label' => 'CDD', 'value' => 'CDD'],
                ['label' => 'Stage', 'value' => 'Stage'],
                ['label' => 'Freelance', 'value' => 'Freelance'],
            ]
        ];

        return BaseResponse::ok($filters)->toJsonResponse();
    }
}