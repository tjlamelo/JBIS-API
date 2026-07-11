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
        return [
            'id' => $this->id,
            'domain' => $this->domain,
            'title' => $this->title,
            'duration_label' => $this->duration_label,
            'organization' => $this->organization,
            'description' => $this->description,
            'cost' => $this->cost,
            'first_installment' => $this->first_installment,
            'second_installment' => $this->second_installment,
            'registration_fee' => $this->registration_fee,
            'currency' => $this->currency,
            'exam_mode' => $this->exam_mode,
            'validity_years' => $this->validity_years,
            'level' => $this->level,
            'process_flow_id' => $this->process_flow_id,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
