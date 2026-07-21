<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Jobs\ExtractUserDocumentJob;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\UserDocumentExtraction;
use App\Core\Domain\Shared\Ai\Enums\DocumentExtractionStatus;
use App\Core\Domain\Shared\Ai\Intel\DocumentExtractionProfileRegistry;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class ReprocessUserDocumentExtractionAction
{
    public function execute(UserDocument $document): UserDocumentExtraction
    {
        if (! (bool) config('ai.document_extraction.enabled', true)) {
            throw new RuntimeException('L\'extraction IA des documents est désactivée.');
        }

        $document->loadMissing('documentType');
        $typeCode = (string) ($document->documentType?->code ?? '');

        if (! DocumentExtractionProfileRegistry::isExtractable($typeCode)) {
            throw new RuntimeException(sprintf('Ce type de document (%s) n\'est pas analysable par l\'IA.', $typeCode));
        }

        if (! DocumentExtractionProfileRegistry::supportsExtractableMime($document->mime_type)) {
            throw new RuntimeException('Format de fichier non pris en charge pour l\'extraction IA (PDF ou image requis).');
        }

        // Annule les extractions en cours / à valider pour éviter les doublons.
        UserDocumentExtraction::query()
            ->where('user_document_id', $document->id)
            ->whereIn('status', [
                DocumentExtractionStatus::Processing->value,
                DocumentExtractionStatus::PendingReview->value,
            ])
            ->update([
                'status' => DocumentExtractionStatus::Rejected->value,
                'error_message' => 'Remplacée par une nouvelle analyse.',
                'reviewed_at' => now(),
            ]);

        $extraction = UserDocumentExtraction::query()->create([
            'user_document_id' => $document->id,
            'user_id' => $document->user_id,
            'document_type_code' => $typeCode,
            'status' => DocumentExtractionStatus::Processing,
        ]);

        $queue = (string) config('ai.document_extraction.queue', 'default');
        ExtractUserDocumentJob::dispatch($document->id)->onQueue($queue);

        Log::info('[document_extraction] Reprocess dispatché', [
            'user_document_id' => $document->id,
            'extraction_id' => $extraction->id,
            'document_type' => $typeCode,
            'queue' => $queue,
        ]);

        return $extraction;
    }
}
