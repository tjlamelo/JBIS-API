<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources\CertificationOffer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CertificationOfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->is('api/v1/catalog/admin/certification-offers*');
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'domain' => $this->domain,
            'title' => $isAdmin
                ? $this->getTranslations('title')
                : ($this->getTranslation('title', $locale) ?: $this->getTranslation('title', 'fr')),
            'duration_label' => $isAdmin
                ? $this->getTranslations('duration_label')
                : ($this->getTranslation('duration_label', $locale) ?: $this->getTranslation('duration_label', 'fr')),
            'organization' => $isAdmin
                ? $this->getTranslations('organization')
                : ($this->getTranslation('organization', $locale) ?: $this->getTranslation('organization', 'fr')),
            'description' => $isAdmin
                ? $this->getTranslations('description')
                : ($this->getTranslation('description', $locale) ?: $this->getTranslation('description', 'fr')),
            'cost' => $this->cost,
            'first_installment' => $this->first_installment,
            'second_installment' => $this->second_installment,
            'registration_fee' => $this->registration_fee,
            'currency' => $this->currency,
            'exam_mode' => $this->exam_mode,
            'validity_years' => $this->validity_years,
            'level' => $isAdmin
                ? $this->getTranslations('level')
                : ($this->getTranslation('level', $locale) ?: $this->getTranslation('level', 'fr')),
            'process_flow_id' => $this->process_flow_id,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
