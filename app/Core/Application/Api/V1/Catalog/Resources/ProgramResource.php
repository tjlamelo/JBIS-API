<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources;

use App\Core\Application\Api\V1\Candidacy\Resources\RequiredDocumentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        
        return [
            'id'          => $this->id,
            'name'        => $this->getTranslation('name', $locale),
            'slug'        => $this->getTranslation('slug', $locale),
            'description' => $this->getTranslation('description', $locale),
            'geographic_zone' => $this->geographicZone?->name,
            
            // 🟢 AJOUTE CETTE LIGNE POUR ENVOYER LES OFFRES AU FRONTEND
            'offers' => OfferShortResource::collection($this->whenLoaded('offers')),

            'required_documents' => RequiredDocumentResource::collection($this->whenLoaded('requiredDocuments')),
            'procedure_cost'     => (float) $this->procedure_cost,
            'currency'           => $this->currency,
            'procedure_duration' => $this->procedure_duration,
            'duration_unit'      => $this->duration_unit,
            'image_url'          => $this->image ? asset('storage/' . $this->image) : null,
            'status'             => $this->status,
            'start_date'         => $this->start_date?->format('Y-m-d'),
            'end_date'           => $this->end_date?->format('Y-m-d'),
            'offers_count'       => $this->whenCounted('offers'),
        ];
    }
}