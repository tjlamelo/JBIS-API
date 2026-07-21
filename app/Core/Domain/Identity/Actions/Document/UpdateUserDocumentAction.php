<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Jobs\ExtractUserDocumentJob;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Services\Document\DocumentStorageService;
use App\Core\Domain\Identity\Services\Document\DocumentTypeResolver;
use App\Core\Domain\Identity\Services\Document\UserDocumentGuardService;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use App\Core\Domain\Shared\Ai\Intel\DocumentExtractionProfileRegistry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

final class UpdateUserDocumentAction
{
    public function __construct(
        private readonly DocumentStorageService $storage,
        private readonly DocumentTypeResolver $documentTypeResolver,
        private readonly UserDocumentGuardService $documentGuard,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(UserDocument $document, array $data, ?UploadedFile $file = null, ?User $actor = null): UserDocument
    {
        if ($actor !== null) {
            $this->documentGuard->assertCandidateCanMutate($document, $actor);
        }

        $allowed = [
            'document_number',
            'issuing_country_id',
            'issue_date',
            'expiry_date',
            'notes',
            'is_verified_copy',
            'is_sensitive',
        ];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $document->{$key} = $data[$key];
            }
        }

        if (array_key_exists('type', $data) && $data['type'] !== null && $data['type'] !== '') {
            $document->document_type_id = $this->documentTypeResolver
                ->resolve((string) $data['type'])
                ->id;
        }

        $document->loadMissing('documentType');
        $type = $document->documentType;

        if (! $type instanceof DocumentType) {
            $type = $this->documentTypeResolver->resolveById((int) $document->document_type_id);
        }

        if ($file !== null) {
            $this->storage->delete($document->file_path);
            $stored = $this->storage->store($file, (int) $document->user_id, $type);
            $document->file_path = $stored->filePath;
            $document->original_filename = $stored->originalFilename;
            $document->mime_type = $stored->mimeType;
            $document->file_size = $stored->fileSize;
            $document->status = UserDocumentStatus::Pending;
            $document->validated_at = null;
            $document->validated_by = null;
            $document->rejection_reason = null;
        }

        $document->save();
        $document = $document->fresh(['issuingCountry', 'user', 'documentType']);

        if ($file !== null) {
            $this->maybeDispatchExtraction($document);
        }

        return $document;
    }

    private function maybeDispatchExtraction(UserDocument $document): void
    {
        $typeCode = (string) ($document->documentType?->code ?? '');
        $extractionEnabled = (bool) config('ai.document_extraction.enabled', true);
        $isExtractable = DocumentExtractionProfileRegistry::isExtractable($typeCode);
        $supportsMime = DocumentExtractionProfileRegistry::supportsExtractableMime($document->mime_type);

        if ($extractionEnabled && $isExtractable && $supportsMime) {
            Log::info('[document_extraction] Job dispatché après remplacement fichier', [
                'user_document_id' => $document->id,
                'document_type' => $typeCode,
                'mime_type' => $document->mime_type,
            ]);
            ExtractUserDocumentJob::dispatch($document->id);
        }
    }
}
