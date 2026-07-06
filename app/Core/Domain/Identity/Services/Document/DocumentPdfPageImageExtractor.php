<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Shared\Pdf\Contracts\PdfProcessorInterface;
use App\Core\Domain\Shared\Pdf\Sources\UserDocumentSource;
use App\Core\Domain\Shared\Pdf\Support\PdfSourceMaterializer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Convertit les premières pages d'un PDF en images (iLovePDF pdfjpg), max N pages.
 */
final class DocumentPdfPageImageExtractor
{
    public function __construct(
        private readonly PdfProcessorInterface $processor,
        private readonly PdfSourceMaterializer $materializer,
    ) {}

    /**
     * @return list<string> chemins absolus des images (page 1..N)
     */
    public function renderFirstPages(UserDocument $document, int $maxPages): array
    {
        $maxPages = max(1, $maxPages);
        $workDir = $this->createWorkDirectory();

        try {
            $taskResult = $this->materializer->withMaterialized(
                [UserDocumentSource::of($document)],
                fn (array $paths) => $this->processor->pdfToImages(
                    file: $paths[0],
                    outputDir: $workDir,
                    outputFilename: 'page',
                ),
            );

            $imagePaths = $this->collectImagePaths($taskResult->path, $taskResult->isArchive, $maxPages);

            Log::info('[document_extraction] Pages PDF converties en images', [
                'user_document_id' => $document->id,
                'pages_rendered' => count($imagePaths),
                'max_pages' => $maxPages,
            ]);

            return $imagePaths;
        } catch (\Throwable $exception) {
            $this->cleanupDirectory($workDir);

            throw $exception;
        }
    }

    /**
     * @param  list<string>  $absolutePaths
     */
    public function cleanupRenderedPages(array $absolutePaths): void
    {
        $directories = [];
        foreach ($absolutePaths as $path) {
            $directories[dirname($path)] = true;
        }

        foreach (array_keys($directories) as $directory) {
            $this->cleanupDirectory($directory);
        }
    }

    private function createWorkDirectory(): string
    {
        $directory = storage_path('app/tmp/pdf-pages/'.uniqid('doc_', true));
        File::ensureDirectoryExists($directory);

        return $directory;
    }

    /**
     * @return list<string>
     */
    private function collectImagePaths(string $outputPath, bool $isArchive, int $maxPages): array
    {
        $searchDir = dirname($outputPath);

        if ($isArchive) {
            $searchDir = $searchDir.'/unzipped';
            File::ensureDirectoryExists($searchDir);
            $zip = new ZipArchive();
            if ($zip->open($outputPath) !== true) {
                throw new \RuntimeException('Impossible de décompresser les pages PDF converties.');
            }
            $zip->extractTo($searchDir);
            $zip->close();
        }

        $files = array_merge(
            File::glob($searchDir.'/*.jpg') ?: [],
            File::glob($searchDir.'/*.jpeg') ?: [],
            File::glob($searchDir.'/*.png') ?: [],
        );

        sort($files, SORT_NATURAL);
        $selected = array_slice($files, 0, $maxPages);

        if ($selected === []) {
            throw new \RuntimeException('Aucune page image n\'a été produite à partir du PDF.');
        }

        return $selected;
    }

    private function cleanupDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            File::deleteDirectory($directory);
        }
    }
}
