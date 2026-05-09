<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramShortResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        
        return [
            'id'   => $this->id,
            'name' => $this->getTranslation('name', $locale) ?? $this->getTranslation('name', 'fr'),
            'slug' => $this->getTranslation('slug', $locale) ?? $this->getTranslation('slug', 'en'),
            
            'geographic_zone' => $this->geographic_zone?->name,
            'procedure_cost'  => (float) $this->procedure_cost,
            'currency'        => $this->currency,
            'duration'        => $this->procedure_duration . ' ' . $this->duration_unit,
            
            'image_url'       => $this->image ? asset('storage/' . $this->image) : null,
            'status'          => $this->status,
            
            'offers_count'    => $this->whenCounted('offers'),
        ];
    }
}