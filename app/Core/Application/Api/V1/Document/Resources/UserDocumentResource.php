<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Document\Resources;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Support\Document\DocumentDownloadNameBuilder;
use App\Core\Domain\Identity\Services\Document\UserDocumentGuardService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserDocument */
final class UserDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $documentType = $this->relationLoaded('documentType') ? $this->documentType : null;

        $downloadFilename = app(DocumentDownloadNameBuilder::class)->forDocument($this->resource);
        $lockState = app(UserDocumentGuardService::class)->lockStateForCandidate($this->resource);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'uploaded_by' => $this->uploaded_by,
            'document_type_id' => $this->document_type_id,
            'type' => $documentType?->code,
            'type_label' => $documentType?->resolvedLabel(),
            'document_number' => $this->document_number,
            'issuing_country_id' => $this->issuing_country_id,
            'issuing_country' => $this->whenLoaded('issuingCountry', fn () => [
                'id' => $this->issuingCountry?->id,
                'name' => $this->issuingCountry?->name,
            ]),
            'file_path' => $this->file_path,
            'original_filename' => $this->original_filename,
            'download_filename' => $downloadFilename,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'url' => $this->url,
            'issue_date' => $this->issue_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'is_expired' => $this->isExpired(),
            'status' => $this->status?->value ?? $this->status,
            'rejection_reason' => $this->rejection_reason,
            'validated_at' => $this->validated_at?->toIso8601String(),
            'validated_by' => $this->validated_by,
            'notes' => $this->notes,
            'is_verified_copy' => $this->is_verified_copy,
            'is_sensitive' => $this->is_sensitive,
            'is_locked_for_candidate' => $lockState['locked'],
            'lock_reason' => $lockState['reason'],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
