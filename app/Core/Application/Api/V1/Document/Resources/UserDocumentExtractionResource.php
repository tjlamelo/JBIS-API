<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Resources;

use App\Core\Domain\Identity\Models\UserDocumentExtraction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserDocumentExtraction */
final class UserDocumentExtractionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_document_id' => $this->user_document_id,
            'user_id' => $this->user_id,
            'document_type_code' => $this->document_type_code,
            'status' => $this->status?->value,
            'draft_payload' => $this->draft_payload,
            'applied_payload' => $this->applied_payload,
            'error_message' => $this->error_message,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'applied_at' => $this->applied_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
