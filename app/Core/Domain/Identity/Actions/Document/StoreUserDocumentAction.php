<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\DTOs\Document\UserDocumentDto;
use App\Core\Domain\Identity\Jobs\ExtractUserDocumentJob;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Services\Document\DocumentStorageService;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use App\Core\Domain\Shared\Ai\Intel\DocumentExtractionProfileRegistry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

final class StoreUserDocumentAction
{
    public function __construct(
        private readonly DocumentStorageService $storage,
        private readonly DeleteUserDocumentAction $deleteUserDocument,
    ) {}

    public function execute(UserDocumentDto $dto, UploadedFile $file, ?User $actor = null): UserDocument
    {
        if ($dto->documentType->isUniquePerUser()) {
            $existingDocuments = UserDocument::query()
                ->where('user_id', $dto->userId)
                ->where('document_type_id', $dto->documentType->id)
                ->get();

            foreach ($existingDocuments as $existingDocument) {
                $this->deleteUserDocument->execute($existingDocument, $actor);
            }
        }

        $stored = $this->storage->store($file, $dto->userId, $dto->documentType);

        $attributes = array_merge($dto->toAttributes(), [
            'file_path' => $stored->filePath,
            'original_filename' => $stored->originalFilename,
            'mime_type' => $stored->mimeType,
            'file_size' => $stored->fileSize,
        ]);

        if ($this->isStaffUploadForCandidate($dto, $actor)) {
            $attributes['status'] = UserDocumentStatus::Approved->value;
            $attributes['validated_at'] = now();
            $attributes['validated_by'] = (int) ($actor?->id ?? $dto->uploadedBy);
        }

        $document = UserDocument::query()->create($attributes);
        $document->setRelation('documentType', $dto->documentType);

        $typeCode = (string) $dto->documentType->code;
        $extractionEnabled = (bool) config('ai.document_extraction.enabled', true);
        $isExtractable = DocumentExtractionProfileRegistry::isExtractable($typeCode);
        $supportsMime = DocumentExtractionProfileRegistry::supportsExtractableMime($stored->mimeType);

        if ($extractionEnabled && $isExtractable && $supportsMime) {
            Log::info('[document_extraction] Job dispatché après upload', [
                'user_document_id' => $document->id,
                'user_id' => $document->user_id,
                'document_type' => $typeCode,
                'mime_type' => $stored->mimeType,
            ]);

            ExtractUserDocumentJob::dispatch($document->id);
        } else {
            Log::info('[document_extraction] Extraction non lancée à l\'upload', [
                'user_document_id' => $document->id,
                'document_type' => $typeCode,
                'mime_type' => $stored->mimeType,
                'enabled' => $extractionEnabled,
                'extractable_type' => $isExtractable,
                'extractable_mime' => $supportsMime,
            ]);
        }

        return $document;
    }

    private function isStaffUploadForCandidate(UserDocumentDto $dto, ?User $actor): bool
    {
        if ($actor === null || $dto->uploadedBy === null) {
            return false;
        }

        return (int) $actor->id !== (int) $dto->userId
            && (int) $dto->uploadedBy === (int) $actor->id;
    }
}
