<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources\Training;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TrainingResource extends JsonResource
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
            'organization' => $this->organization,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'duration_hours' => $this->duration_hours,
            'duration_days' => $this->duration_days,
            'mode' => $this->mode,
            'location' => $this->location,
            'prerequisites' => $this->prerequisites,
            'is_certified' => (bool) $this->is_certified,
            'certificate_name' => $this->certificate_name,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
