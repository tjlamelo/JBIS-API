<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Services\Document\DocumentPdfPageImageExtractor;
use App\Core\Domain\Identity\Services\Document\DocumentPdfTextExtractor;
use App\Core\Domain\Identity\Services\Document\DocumentVisionInputResolver;
use Illuminate\Support\Facades\Log;

/**
 * Orchestre l'extraction IA : images, PDF texte natif, ou PDF scanné (vision sur max N pages).
 */
final class UserDocumentExtractionService
{
    public function __construct(
        private readonly UserDocumentVisionExtractionService $visionExtraction,
        private readonly DocumentTextExtractionService $textExtraction,
        private readonly DocumentPdfTextExtractor $pdfTextExtractor,
        private readonly DocumentPdfPageImageExtractor $pdfPageImageExtractor,
        private readonly DocumentVisionInputResolver $visionInputResolver,
        private readonly DocumentExtractionDraftEnricher $draftEnricher,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function extract(UserDocument $document): array
    {
        $document->loadMissing('documentType');
        $typeCode = (string) ($document->documentType?->code ?? 'CV');
        $mimeType = strtolower((string) ($document->mime_type ?? ''));

        $draft = match (true) {
            str_starts_with($mimeType, 'image/') => $this->visionExtraction->extractFromDocument($document),
            $mimeType === 'application/pdf' => $this->extractFromPdf($document, $typeCode),
            default => throw new \InvalidArgumentException(sprintf('MIME non pris en charge pour l\'extraction : %s', $mimeType)),
        };

        return $this->draftEnricher->enrich($draft, $typeCode);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractFromPdf(UserDocument $document, string $typeCode): array
    {
        if (! (bool) config('ai.document_extraction.pdf.enabled', true)) {
            throw new \RuntimeException('L\'extraction IA des PDF est désactivée (AI_DOCUMENT_EXTRACTION_PDF_ENABLED).');
        }

        $maxPages = max(1, (int) config('ai.document_extraction.pdf.max_pages', 2));
        $minTextChars = max(1, (int) config('ai.document_extraction.pdf.min_text_chars', 200));

        $text = $this->pdfTextExtractor->extractFirstPages($document, $maxPages);
        if (mb_strlen(trim($text)) >= $minTextChars) {
            Log::info('[document_extraction] Pipeline PDF texte', [
                'user_document_id' => $document->id,
                'char_count' => mb_strlen($text),
                'max_pages' => $maxPages,
            ]);

            return $this->textExtraction->extractDraft($typeCode, $text);
        }

        Log::info('[document_extraction] Pipeline PDF vision (scan)', [
            'user_document_id' => $document->id,
            'max_pages' => $maxPages,
        ]);

        $renderedPages = $this->pdfPageImageExtractor->renderFirstPages($document, $maxPages);

        try {
            $imageInputs = array_map(
                fn (string $absolutePath): string => $this->visionInputResolver->fromAbsolutePath($absolutePath, 'image/jpeg'),
                $renderedPages,
            );

            return $this->visionExtraction->extractFromImageInputs($document, $imageInputs);
        } finally {
            $this->pdfPageImageExtractor->cleanupRenderedPages($renderedPages);
        }
    }
}
