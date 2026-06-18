<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Domain\Identity\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Education */
final class EducationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'education_level_id' => $this->education_level_id,
            'document_id' => $this->document_id,
            'degree' => $this->degree,
            'institution_name' => $this->institution_name,
            'field_of_study' => $this->field_of_study,
            'country_id' => $this->country_id,
            'residence_city' => $this->residence_city,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_current' => $this->is_current,
            'grade' => $this->grade,
            'is_approved' => $this->is_approved,
            'approved_by' => $this->approved_by,
            'level' => $this->whenLoaded('level', fn () => [
                'id' => $this->level?->id,
                'name' => $this->level?->name ?? null,
            ]),
            'country' => $this->whenLoaded('country', fn () => [
                'id' => $this->country?->id,
                'name' => $this->country?->name ?? null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
