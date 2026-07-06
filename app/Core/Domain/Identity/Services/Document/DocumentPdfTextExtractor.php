<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

use App\Core\Domain\Identity\Models\UserDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

/**
 * Extrait le texte natif d'un PDF (pages numériques), limité aux N premières pages.
 */
final class DocumentPdfTextExtractor
{
    public function extractFirstPages(UserDocument $document, int $maxPages): string
    {
        $filePath = (string) ($document->file_path ?? '');
        if ($filePath === '' || ! Storage::disk(UserDocument::STORAGE_DISK)->exists($filePath)) {
            return '';
        }

        $absolutePath = Storage::disk(UserDocument::STORAGE_DISK)->path($filePath);
        $maxPages = max(1, $maxPages);

        try {
            $pdf = (new Parser())->parseFile($absolutePath);
            $pages = $pdf->getPages();
            $chunks = [];

            foreach (array_slice($pages, 0, $maxPages) as $index => $page) {
                $text = trim((string) $page->getText());
                if ($text !== '') {
                    $chunks[] = sprintf("--- Page %d ---\n%s", $index + 1, $text);
                }
            }

            $aggregated = trim(implode("\n\n", $chunks));

            Log::info('[document_extraction] Texte PDF extrait', [
                'user_document_id' => $document->id,
                'pages_read' => min(count($pages), $maxPages),
                'char_count' => mb_strlen($aggregated),
            ]);

            return $aggregated;
        } catch (\Throwable $exception) {
            Log::info('[document_extraction] Extraction texte PDF impossible', [
                'user_document_id' => $document->id,
                'message' => $exception->getMessage(),
            ]);

            return '';
        }
    }
}
