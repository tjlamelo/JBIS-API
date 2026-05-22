<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources\Offer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferShortResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // On récupère la locale actuelle (fr, en...)
        $locale = app()->getLocale();

        return [
            'id' => $this->id,

            // ✅ CORRECTION : Utiliser 'title' car 'name' n'existe pas sur le modèle Offer
            'title' => $this->getTranslations('title'),
            'slug' => $this->getTranslations('slug'),

            // Localisation
            'city' => [
                'name' => $this->city?->name, // Traduit automatiquement par Spatie sur le modèle City
            ],
            'country' => [
                'code' => $this->country?->code,
                'name' => $this->country?->name,
            ],

            // Données financières
            'salary_min' => $this->is_salary_public ? $this->salary_min : null,
            'currency' => $this->currency ?? 'XAF',
            'is_salary_public' => (bool) $this->is_salary_public,

            // Contrat
            'contract_type' => [
                'name' => $this->contractType?->name,
                'color_code' => $this->contractType?->color_code,
            ],

            // Entreprise
            'company' => [
                'name' => $this->is_company_public ? ($this->company?->name ?? __('Partenaire')) : __('Confidentiel'),
                'logo' => $this->is_company_public ? $this->company?->logo : null,
            ],
            'is_company_public' => (bool) $this->is_company_public,

            'published_at' => $this->published_at?->diffForHumans(),

            // Meta pour le badge "Urgent"
            'meta' => [
                'is_urgent' => (bool) ($this->meta['is_urgent'] ?? false),
            ],
        ];
    }
}
