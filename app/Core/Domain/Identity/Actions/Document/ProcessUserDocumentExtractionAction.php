<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\UserDocumentExtraction;
use App\Core\Domain\Shared\Ai\Enums\DocumentExtractionStatus;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelRateLimitedException;
use App\Core\Domain\Shared\Ai\Intel\DocumentExtractionProfileRegistry;
use App\Core\Domain\Shared\Ai\Intel\UserDocumentExtractionService;
use Illuminate\Support\Facades\Log;

final class ProcessUserDocumentExtractionAction
{
    public function __construct(
        private readonly UserDocumentExtractionService $extractionService,
    ) {}

    public function execute(int $userDocumentId): ?UserDocumentExtraction
    {
        Log::info('[document_extraction] Traitement démarré', [
            'user_document_id' => $userDocumentId,
        ]);

        $document = UserDocument::query()->with('documentType')->find($userDocumentId);
        if ($document === null) {
            Log::warning('[document_extraction] Document introuvable', [
                'user_document_id' => $userDocumentId,
            ]);

            return null;
        }

        $typeCode = (string) ($document->documentType?->code ?? '');
        if (! DocumentExtractionProfileRegistry::isExtractable($typeCode)) {
            Log::info('[document_extraction] Type non extractable', [
                'user_document_id' => $userDocumentId,
                'document_type' => $typeCode,
            ]);

            return null;
        }

        if (! DocumentExtractionProfileRegistry::supportsExtractableMime($document->mime_type)) {
            Log::info('[document_extraction] MIME non supporté', [
                'user_document_id' => $userDocumentId,
                'document_type' => $typeCode,
                'mime_type' => $document->mime_type,
            ]);

            return null;
        }

        // Réutilise une extraction "processing" récente si le job a été relancé.
        $extraction = UserDocumentExtraction::query()
            ->where('user_document_id', $document->id)
            ->where('status', DocumentExtractionStatus::Processing)
            ->latest('id')
            ->first();

        if ($extraction === null) {
            $extraction = UserDocumentExtraction::query()->create([
                'user_document_id' => $document->id,
                'user_id' => $document->user_id,
                'document_type_code' => $typeCode,
                'status' => DocumentExtractionStatus::Processing,
            ]);
        }

        Log::info('[document_extraction] Enregistrement extraction créé', [
            'user_document_id' => $document->id,
            'extraction_id' => $extraction->id,
            'document_type' => $typeCode,
        ]);

        try {
            $draft = $this->extractionService->extract($document);

            $extraction->update([
                'status' => DocumentExtractionStatus::PendingReview,
                'draft_payload' => $draft,
                'error_message' => null,
            ]);

            Log::info('[document_extraction] Extraction réussie', [
                'user_document_id' => $document->id,
                'extraction_id' => $extraction->id,
                'draft_keys' => array_keys($draft),
            ]);
        } catch (LanguageModelRateLimitedException $exception) {
            $extraction->update([
                'status' => DocumentExtractionStatus::Processing,
                'error_message' => mb_substr($exception->getMessage(), 0, 500),
            ]);

            Log::warning('[document_extraction] Rate limit — retry job', [
                'user_document_id' => $document->id,
                'extraction_id' => $extraction->id,
                'retry_after' => $exception->retryAfterSeconds,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        } catch (\Throwable $exception) {
            Log::warning('[document_extraction] Extraction échouée', [
                'user_document_id' => $document->id,
                'extraction_id' => $extraction->id,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            $message = $exception->getMessage();
            if (! is_string($message) || $message === '') {
                $message = 'Erreur inconnue pendant l\'analyse du document.';
            }
            if (str_contains($message, 'Array to string conversion')) {
                $message = 'L\'analyse IA a renvoyé un format inattendu. Merci de réessayer ou de vérifier le document.';
            }

            $extraction->update([
                'status' => DocumentExtractionStatus::Failed,
                'error_message' => mb_substr($message, 0, 500),
            ]);
        }

        return $extraction->fresh();
    }
}
