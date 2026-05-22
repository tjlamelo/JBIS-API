<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources\Program;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramShortResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name', $locale) ?? $this->getTranslation('name', 'fr'),
            'slug' => $this->getTranslation('slug', $locale) ?? $this->getTranslation('slug', 'en'),

            'geographic_zone' => $this->geographicZone?->name,
            'procedure_duration' => $this->procedure_duration,
            'duration_unit' => $this->duration_unit,
            'age_min' => $this->age_min,
            'age_max' => $this->age_max,
            'is_featured' => (bool) $this->is_featured,
            'is_urgent' => (bool) $this->is_urgent,
            'views_count' => (int) $this->views_count,

            'image' => $this->image,
            'status' => $this->status,

            'offers_count' => $this->whenCounted('offers'),
        ];
    }
}
