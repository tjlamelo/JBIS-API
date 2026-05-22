<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Services;

use App\Core\Domain\Shared\Pdf\Contracts\PdfProcessorInterface;
use App\Core\Domain\Shared\Pdf\DTOs\PdfTaskResult;
use App\Core\Domain\Shared\Pdf\DTOs\PublishedPdfResult;
use App\Core\Domain\Shared\Pdf\Enums\CompressLevel;
use App\Core\Domain\Shared\Pdf\Exceptions\PdfProcessingException;
use App\Core\Domain\Shared\Pdf\Sources\PdfSource;
use App\Core\Domain\Shared\Pdf\Support\PdfSourceMaterializer;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Façade haut-niveau pour traiter les documents de la plateforme avec iLovePDF.
 *
 * Pipeline :
 *
 *  1. Sources (UserDocument / Archive / chemin disque) sont matérialisées
 *     localement par `PdfSourceMaterializer`.
 *  2. Le résultat brut est produit par `PdfProcessorInterface` dans un dossier
 *     tmp (`storage/app/tmp/pdf/out/...`).
 *  3. Le résultat est publié sur le disque cible (par défaut `jbis_assets`,
 *     dossier `documents/processed/...`).
 *  4. Les fichiers temporaires (sources + sortie locale) sont supprimés.
 *
 * Cloudinary n'est jamais touché ici : `jbis_assets` est la source de vérité
 * pour le traitement, Cloudinary reste un CDN d'affichage géré ailleurs.
 */
final class PdfDocumentService
{
    public function __construct(
        private readonly PdfProcessorInterface $processor,
        private readonly PdfSourceMaterializer $materializer,
        private readonly string $defaultDisk = 'jbis_assets',
        private readonly string $defaultFolder = 'documents/processed',
    ) {}

    public function compress(
        PdfSource $source,
        ?CompressLevel $level = null,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        return $this->materializer->withMaterialized(
            [$source],
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->compress(
                    files: $paths,
                    outputDir: $tmpOutDir,
                    level: $level,
                    outputFilename: $this->resolveOutputName($filename, $source->originalName(), 'compressed'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    /**
     * @param  list<PdfSource>  $sources
     */
    public function merge(
        array $sources,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        if (count($sources) < 2) {
            throw new PdfProcessingException('Merge requires at least two sources.');
        }

        return $this->materializer->withMaterialized(
            $sources,
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->merge(
                    files: $paths,
                    outputDir: $tmpOutDir,
                    outputFilename: $this->resolveOutputName($filename, $sources[0]->originalName(), 'merged'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    public function protect(
        PdfSource $source,
        string $password,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        return $this->materializer->withMaterialized(
            [$source],
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->protect(
                    file: $paths[0],
                    outputDir: $tmpOutDir,
                    password: $password,
                    outputFilename: $this->resolveOutputName($filename, $source->originalName(), 'protected'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    public function unlock(
        PdfSource $source,
        string $password,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        return $this->materializer->withMaterialized(
            [$source],
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->unlock(
                    file: $paths[0],
                    outputDir: $tmpOutDir,
                    password: $password,
                    outputFilename: $this->resolveOutputName($filename, $source->originalName(), 'unlocked'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    public function watermark(
        PdfSource $source,
        string $text,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        return $this->materializer->withMaterialized(
            [$source],
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->watermark(
                    files: $paths,
                    outputDir: $tmpOutDir,
                    text: $text,
                    outputFilename: $this->resolveOutputName($filename, $source->originalName(), 'watermarked'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    public function split(
        PdfSource $source,
        string $ranges,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        return $this->materializer->withMaterialized(
            [$source],
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->split(
                    file: $paths[0],
                    outputDir: $tmpOutDir,
                    ranges: $ranges,
                    outputFilename: $this->resolveOutputName($filename, $source->originalName(), 'split'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    public function officeToPdf(
        PdfSource $source,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        return $this->materializer->withMaterialized(
            [$source],
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->officeToPdf(
                    file: $paths[0],
                    outputDir: $tmpOutDir,
                    outputFilename: $this->resolveOutputName($filename, $source->originalName(), 'converted'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    public function pdfToImages(
        PdfSource $source,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        return $this->materializer->withMaterialized(
            [$source],
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->pdfToImages(
                    file: $paths[0],
                    outputDir: $tmpOutDir,
                    outputFilename: $this->resolveOutputName($filename, $source->originalName(), 'images'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    public function ocr(
        PdfSource $source,
        ?string $folder = null,
        ?string $filename = null,
        ?string $disk = null,
    ): PublishedPdfResult {
        return $this->materializer->withMaterialized(
            [$source],
            fn (array $paths) => $this->runAndPublish(
                fn (string $tmpOutDir) => $this->processor->ocr(
                    files: $paths,
                    outputDir: $tmpOutDir,
                    outputFilename: $this->resolveOutputName($filename, $source->originalName(), 'ocr'),
                ),
                $disk ?? $this->defaultDisk,
                $folder ?? $this->defaultFolder,
            ),
        );
    }

    /**
     * Exécute le processor dans un dossier tmp, puis publie le fichier produit
     * sur le disque cible, en garantissant le nettoyage du tmp dans tous les cas.
     *
     * @param  callable(string $tmpOutDir): PdfTaskResult  $process
     */
    private function runAndPublish(callable $process, string $disk, string $folder): PublishedPdfResult
    {
        $tmpOutDir = $this->makeTemporaryOutputDir();

        try {
            $taskResult = $process($tmpOutDir);

            return $this->publish($taskResult, $disk, $folder);
        } finally {
            $this->removeDirectory($tmpOutDir);
        }
    }

    private function publish(PdfTaskResult $task, string $diskName, string $folder): PublishedPdfResult
    {
        $disk = Storage::disk($diskName);

        $folder = trim($folder, '/');
        $relativeFolder = $folder === ''
            ? date('Y/m')
            : $folder.'/'.date('Y/m');

        $relativePath = $relativeFolder.'/'.$task->filename;

        $stream = fopen($task->path, 'rb');
        if ($stream === false) {
            throw new PdfProcessingException(
                sprintf('Unable to open generated file for publication: %s', $task->path)
            );
        }

        try {
            $written = $disk->put($relativePath, $stream, [
                'visibility' => 'public',
                'ContentType' => $task->mimeType,
            ]);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($written === false) {
            throw new PdfProcessingException(
                sprintf('Failed to publish PDF result to %s://%s', $diskName, $relativePath)
            );
        }

        return new PublishedPdfResult(
            task: $task,
            disk: $diskName,
            relativePath: $relativePath,
            publicUrl: $this->resolvePublicUrl($disk, $relativePath),
        );
    }

    private function resolvePublicUrl(Filesystem $disk, string $relativePath): ?string
    {
        if (! $disk instanceof FilesystemAdapter) {
            return null;
        }

        try {
            return $disk->url($relativePath);
        } catch (Throwable) {
            return null;
        }
    }

    private function makeTemporaryOutputDir(): string
    {
        $dir = storage_path('app/tmp/pdf/out/'.Str::uuid()->toString());
        if (! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw PdfProcessingException::invalidOutputDirectory($dir);
        }

        return $dir;
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $entries = @scandir($dir) ?: [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$entry;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    private function resolveOutputName(?string $explicit, string $originalName, string $suffix): string
    {
        if (is_string($explicit) && $explicit !== '') {
            return pathinfo($explicit, PATHINFO_FILENAME);
        }

        $base = pathinfo($originalName, PATHINFO_FILENAME) ?: 'document';
        $slug = Str::slug($base) ?: 'document';

        return $slug.'-'.$suffix.'-'.Str::random(6);
    }
}
