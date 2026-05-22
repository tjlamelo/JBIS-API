<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources\Program;

use App\Core\Application\Api\V1\Candidacy\Resources\RequiredDocumentResource;
use App\Core\Application\Api\V1\Catalog\Resources\Offer\OfferShortResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($request->is('api/v1/catalog/admin/programs*')) {
            return $this->toAdminArray($request);
        }

        $locale = app()->getLocale();
        $imageUrls = $this->image;

        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name', $locale),
            'slug' => $this->getTranslation('slug', $locale),
            'description' => $this->getTranslation('description', $locale),
            'geographic_zone' => $this->geographicZone?->name,

            'offers' => OfferShortResource::collection($this->whenLoaded('offers')),

            'required_documents' => RequiredDocumentResource::collection($this->whenLoaded('requiredDocuments')),
            'procedure_duration' => $this->procedure_duration,
            'duration_unit' => $this->duration_unit,
            'age_min' => $this->age_min,
            'age_max' => $this->age_max,
            'is_featured' => (bool) $this->is_featured,
            'is_urgent' => (bool) $this->is_urgent,
            'views_count' => (int) $this->views_count,
            'photo_url' => $imageUrls['url'] ?? null,
            'photo_fallback_url' => $imageUrls['fallback_url'] ?? null,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'offers_count' => $this->whenCounted('offers'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toAdminArray(Request $request): array
    {
        $imageUrls = $this->image;

        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'slug' => $this->getTranslations('slug'),
            'description' => $this->getTranslations('description'),

            'geographic_zone_id' => $this->geographic_zone_id,
            'geographic_zone' => $this->geographicZone ? [
                'id' => $this->geographicZone->id,
                'name' => $this->geographicZone->name,
                'slug' => $this->geographicZone->slug,
            ] : null,

            'procedure_duration' => $this->procedure_duration,
            'duration_unit' => $this->duration_unit,
            'age_min' => $this->age_min,
            'age_max' => $this->age_max,
            'is_featured' => (bool) $this->is_featured,
            'is_urgent' => (bool) $this->is_urgent,
            'views_count' => (int) $this->views_count,

            'photo_url' => $imageUrls['url'] ?? null,
            'photo_fallback_url' => $imageUrls['fallback_url'] ?? null,
            'image_media' => $this->image_media,

            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'published_at' => $this->published_at?->toIso8601String(),
            'user_id' => $this->user_id,

            'required_documents' => RequiredDocumentResource::collection($this->whenLoaded('requiredDocuments')),
            'offers' => OfferShortResource::collection($this->whenLoaded('offers')),
            'offers_count' => $this->whenCounted('offers'),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
