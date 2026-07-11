<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Resources;

use App\Core\Domain\Partner\Models\PartnerCohortStudentDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PartnerCohortStudentDocument */
final class PartnerCohortStudentDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type_code' => $this->document_type_code,
            'status' => $this->status?->value ?? $this->status,
            'user_document_id' => $this->user_document_id,
            'validated_at' => $this->validated_at?->toIso8601String(),
            'staff_note' => $this->staff_note,
            'user_document' => $this->whenLoaded('userDocument', fn () => [
                'id' => $this->userDocument?->id,
                'status' => $this->userDocument?->status,
                'original_filename' => $this->userDocument?->original_filename,
            ]),
        ];
    }
}
