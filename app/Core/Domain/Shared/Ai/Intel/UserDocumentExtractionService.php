<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Services\Document\CvSourceTextPreparer;
use App\Core\Domain\Identity\Services\Document\DocumentPdfPageImageExtractor;
use App\Core\Domain\Identity\Services\Document\DocumentPdfTextExtractor;
use App\Core\Domain\Identity\Services\Document\DocumentVisionInputResolver;
use App\Core\Domain\Identity\Services\Document\GoogleCloudVisionOcrService;
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
        private readonly GoogleCloudVisionOcrService $visionOcr,
        private readonly CvSourceTextPreparer $cvSourceTextPreparer,
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

        $maxPages = max(1, (int) config('ai.document_extraction.pdf.max_pages', 4));
        $minTextChars = max(1, (int) config('ai.document_extraction.pdf.min_text_chars', 200));

        if ($typeCode === 'CV') {
            return $this->extractCvFromPdf($document, $maxPages, $minTextChars);
        }

        $text = $this->pdfTextExtractor->extractFirstPages($document, $maxPages);
        $textChars = mb_strlen(trim($text));

        if ($textChars >= $minTextChars) {
            Log::info('[document_extraction] Pipeline PDF texte', [
                'user_document_id' => $document->id,
                'document_type' => $typeCode,
                'char_count' => $textChars,
                'max_pages' => $maxPages,
            ]);

            $draft = $this->textExtraction->extractDraft($typeCode, $text);

            // CV : le texte natif peut contenir l'en-tête (identité) mais pas le corps
            // (PDF Canva/Word → texte partiel). Si le parcours est vide, on repasse en vision.
            if ($typeCode === 'CV' && $this->isCvDraftIncomplete($draft, $text)) {
                Log::info('[document_extraction] CV texte incomplet → repli vision', [
                    'user_document_id' => $document->id,
                    'section_counts' => $this->sectionCounts($draft),
                ]);

                return $this->extractPdfViaVision($document, $maxPages);
            }

            return $draft;
        }

        Log::info('[document_extraction] Pipeline PDF vision (scan)', [
            'user_document_id' => $document->id,
            'document_type' => $typeCode,
            'max_pages' => $maxPages,
            'text_chars' => $textChars,
        ]);

        return $this->extractPdfViaVision($document, $maxPages);
    }

    /**
     * CV PDF : OCR Vision + texte natif → structuration LLM ; repli vision Gemini si incomplet.
     *
     * @return array<string, mixed>
     */
    private function extractCvFromPdf(UserDocument $document, int $maxPages, int $minTextChars): array
    {
        $renderedPages = $this->pdfPageImageExtractor->renderFirstPages($document, $maxPages);

        try {
            $nativeText = $this->pdfTextExtractor->extractFirstPages($document, $maxPages);
            $ocrText = $this->visionOcr->isEnabled()
                ? $this->visionOcr->extractFromImagePaths($renderedPages)
                : '';
            $sourceText = $this->cvSourceTextPreparer->prepare($nativeText, $ocrText);
            $sourceChars = mb_strlen(trim($sourceText));

            if ($sourceChars >= $minTextChars) {
                Log::info('[document_extraction] Pipeline CV PDF OCR + texte', [
                    'user_document_id' => $document->id,
                    'char_count' => $sourceChars,
                    'ocr_enabled' => $this->visionOcr->isEnabled(),
                    'ocr_chars' => mb_strlen(trim($ocrText)),
                    'native_chars' => mb_strlen(trim($nativeText)),
                ]);

                $textDraft = $this->textExtraction->extractDraft('CV', $sourceText);

                if (! $this->isCvDraftIncomplete($textDraft, $sourceText)) {
                    return $textDraft;
                }

                Log::info('[document_extraction] CV OCR+texte incomplet → repli vision', [
                    'user_document_id' => $document->id,
                    'section_counts' => $this->sectionCounts($textDraft),
                ]);

                $visionDraft = $this->extractFromRenderedPages($document, $renderedPages);

                return $this->mergeCvDrafts($visionDraft, $textDraft);
            }

            Log::info('[document_extraction] Pipeline CV PDF vision (texte insuffisant)', [
                'user_document_id' => $document->id,
                'source_chars' => $sourceChars,
                'min_text_chars' => $minTextChars,
            ]);

            return $this->extractFromRenderedPages($document, $renderedPages);
        } finally {
            $this->pdfPageImageExtractor->cleanupRenderedPages($renderedPages);
        }
    }

    /**
     * Fusionne deux brouillons CV : vision (prioritaire) + texte natif (complément).
     *
     * @param  array<string, mixed>  $primary
     * @param  array<string, mixed>  $secondary
     * @return array<string, mixed>
     */
    private function mergeCvDrafts(array $primary, array $secondary): array
    {
        $merged = $primary;

        $profilePrimary = is_array($primary['user_profile'] ?? null) ? $primary['user_profile'] : [];
        $profileSecondary = is_array($secondary['user_profile'] ?? null) ? $secondary['user_profile'] : [];

        foreach ($profileSecondary as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (! isset($profilePrimary[$key]) || $profilePrimary[$key] === '' || $profilePrimary[$key] === null) {
                $profilePrimary[$key] = $value;
            }
        }

        $merged['user_profile'] = $profilePrimary;

        foreach (['educations', 'experiences', 'internships', 'certifications', 'languages', 'skills', 'formations', 'interests'] as $listKey) {
            $primaryRows = is_array($primary[$listKey] ?? null) ? $primary[$listKey] : [];
            $secondaryRows = is_array($secondary[$listKey] ?? null) ? $secondary[$listKey] : [];
            $merged[$listKey] = array_merge($primaryRows, $secondaryRows);
        }

        $mergedNotes = array_filter([
            is_string($primary['notes'] ?? null) ? trim($primary['notes']) : '',
            is_string($secondary['notes'] ?? null) ? trim($secondary['notes']) : '',
        ]);
        $merged['notes'] = implode("\n", $mergedNotes);

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractPdfViaVision(UserDocument $document, int $maxPages): array
    {
        $renderedPages = $this->pdfPageImageExtractor->renderFirstPages($document, $maxPages);

        try {
            return $this->extractFromRenderedPages($document, $renderedPages);
        } finally {
            $this->pdfPageImageExtractor->cleanupRenderedPages($renderedPages);
        }
    }

    /**
     * @param  list<string>  $renderedPages
     * @return array<string, mixed>
     */
    private function extractFromRenderedPages(UserDocument $document, array $renderedPages): array
    {
        $imageInputs = array_map(
            fn (string $absolutePath): string => $this->visionInputResolver->fromAbsolutePath($absolutePath, 'image/jpeg'),
            $renderedPages,
        );

        return $this->visionExtraction->extractFromImageInputs($document, $imageInputs);
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function isCvDraftIncomplete(array $draft, string $sourceText): bool
    {
        $experienceCount = $this->nonEmptyCount($draft['experiences'] ?? null);
        $educationCount = $this->nonEmptyCount($draft['educations'] ?? null);
        $internshipCount = $this->nonEmptyCount($draft['internships'] ?? null);
        $skillCount = $this->nonEmptyCount($draft['skills'] ?? null);
        $languageCount = $this->nonEmptyCount($draft['languages'] ?? null);

        if ($experienceCount === 0
            && $educationCount === 0
            && $internshipCount === 0
            && $skillCount === 0
            && $languageCount === 0) {
            return true;
        }

        $normalizedText = mb_strtolower($sourceText);
        $hasExperienceSignals = preg_match('/\b(experience|experiences|professional experience|employment|work history|expérience|expériences)\b/u', $normalizedText) === 1;
        $hasEducationSignals = preg_match('/\b(education|educations|formation|formations|dipl[oô]me|degree|university|universit[eé])\b/u', $normalizedText) === 1;
        $hasSkillSignals = preg_match('/\b(skill|skills|competencies|competences|compétences|core competencies)\b/u', $normalizedText) === 1;
        $hasLanguageSignals = preg_match('/\b(language|languages|langue|langues|bilingual|anglais|english|fran[cç]ais|french)\b/u', $normalizedText) === 1;

        return ($hasExperienceSignals && $experienceCount === 0 && $internshipCount === 0)
            || ($hasEducationSignals && $educationCount === 0)
            || ($hasSkillSignals && $skillCount === 0)
            || ($hasLanguageSignals && $languageCount === 0);
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, int>
     */
    private function sectionCounts(array $draft): array
    {
        return [
            'experiences' => $this->nonEmptyCount($draft['experiences'] ?? null),
            'educations' => $this->nonEmptyCount($draft['educations'] ?? null),
            'internships' => $this->nonEmptyCount($draft['internships'] ?? null),
            'skills' => $this->nonEmptyCount($draft['skills'] ?? null),
            'languages' => $this->nonEmptyCount($draft['languages'] ?? null),
        ];
    }

    private function nonEmptyCount(mixed $value): int
    {
        if (! is_array($value)) {
            return 0;
        }

        $count = 0;
        foreach ($value as $row) {
            if (is_array($row) && $row !== []) {
                $count++;
            }
        }

        return $count;
    }
}
