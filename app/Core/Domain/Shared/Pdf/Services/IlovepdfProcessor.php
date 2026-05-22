<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Services;

use App\Core\Domain\Shared\Pdf\Contracts\PdfProcessorInterface;
use App\Core\Domain\Shared\Pdf\DTOs\PdfTaskResult;
use App\Core\Domain\Shared\Pdf\Enums\CompressLevel;
use App\Core\Domain\Shared\Pdf\Enums\PdfTool;
use App\Core\Domain\Shared\Pdf\Exceptions\PdfConfigurationException;
use App\Core\Domain\Shared\Pdf\Exceptions\PdfProcessingException;
use Ilovepdf\CompressTask;
use Ilovepdf\Ilovepdf;
use Ilovepdf\OfficepdfTask;
use Ilovepdf\PdfjpgTask;
use Ilovepdf\PdfocrTask;
use Ilovepdf\ProtectTask;
use Ilovepdf\SplitTask;
use Ilovepdf\Task;
use Ilovepdf\UnlockTask;
use Ilovepdf\WatermarkTask;
use Throwable;

/**
 * Implémentation iLovePDF du contrat `PdfProcessorInterface`.
 *
 *  - Sans état (singleton OK) : chaque appel crée un nouveau client + une nouvelle task.
 *  - Toutes les erreurs SDK sont enveloppées en `PdfProcessingException`.
 *  - L'écriture finale se fait dans `$outputDir` (créé si absent) via
 *    `$task->download($outputDir)` qui s'appuie sur le `setOutputFilename` /
 *    `setPackagedFilename` configuré au préalable.
 */
final class IlovepdfProcessor implements PdfProcessorInterface
{
    public function __construct(
        private readonly ?string $publicKey,
        private readonly ?string $secretKey,
        private readonly array $defaults = [],
        private readonly string $defaultCompressionLevel = 'recommended',
    ) {}

    public function compress(
        array $files,
        string $outputDir,
        ?CompressLevel $level = null,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        $resolvedLevel = $level ?? CompressLevel::fromConfig($this->defaultCompressionLevel);

        return $this->run(
            PdfTool::Compress,
            $files,
            $outputDir,
            $outputFilename,
            static function (Task $task) use ($resolvedLevel): void {
                /** @var CompressTask $task */
                $task->setCompressionLevel($resolvedLevel->value);
            },
        );
    }

    public function merge(
        array $files,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        if (count($files) < 2) {
            throw new PdfProcessingException(
                'Merge requires at least two input PDF files.'
            );
        }

        return $this->run(PdfTool::Merge, $files, $outputDir, $outputFilename);
    }

    public function split(
        string $file,
        string $outputDir,
        string $ranges,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        $trimmed = trim($ranges);
        if ($trimmed === '') {
            throw new PdfProcessingException('Split ranges cannot be empty.');
        }

        return $this->run(
            PdfTool::Split,
            [$file],
            $outputDir,
            $outputFilename,
            static function (Task $task) use ($trimmed): void {
                /** @var SplitTask $task */
                $task->setRanges($trimmed);
            },
        );
    }

    public function protect(
        string $file,
        string $outputDir,
        string $password,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        if ($password === '') {
            throw new PdfProcessingException('Password is required to protect a PDF.');
        }

        return $this->run(
            PdfTool::Protect,
            [$file],
            $outputDir,
            $outputFilename,
            static function (Task $task) use ($password): void {
                /** @var ProtectTask $task */
                $task->setPassword($password);
            },
        );
    }

    public function unlock(
        string $file,
        string $outputDir,
        string $password,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        return $this->run(
            PdfTool::Unlock,
            [$file],
            $outputDir,
            $outputFilename,
            static function (Task $task) use ($password): void {
                /** @var UnlockTask $task */
                $task->setPassword($password);
            },
        );
    }

    public function watermark(
        array $files,
        string $outputDir,
        string $text,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        if (trim($text) === '') {
            throw new PdfProcessingException('Watermark text cannot be empty.');
        }

        return $this->run(
            PdfTool::Watermark,
            $files,
            $outputDir,
            $outputFilename,
            static function (Task $task) use ($text): void {
                /** @var WatermarkTask $task */
                $task->setMode('text');
                $task->setText($text);
            },
        );
    }

    public function imagesToPdf(
        array $images,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        return $this->run(PdfTool::ImageToPdf, $images, $outputDir, $outputFilename);
    }

    public function pdfToImages(
        string $file,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        return $this->run(
            PdfTool::PdfToJpg,
            [$file],
            $outputDir,
            $outputFilename,
            static function (Task $task): void {
                /** @var PdfjpgTask $task */
                $task->setMode('pages');
            },
        );
    }

    public function officeToPdf(
        string $file,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        return $this->run(
            PdfTool::OfficeToPdf,
            [$file],
            $outputDir,
            $outputFilename,
            static function (Task $task): void {
                /** @var OfficepdfTask $task */
                // No-op : pas de réglage obligatoire côté officepdf.
            },
        );
    }

    public function ocr(
        array $files,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult {
        return $this->run(
            PdfTool::PdfOcr,
            $files,
            $outputDir,
            $outputFilename,
            static function (Task $task): void {
                /** @var PdfocrTask $task */
                // langues laissées à la valeur par défaut côté SDK.
            },
        );
    }

    /**
     * Exécute une tâche iLovePDF de bout en bout.
     *
     * @param  list<string>  $files
     * @param  (callable(Task):void)|null  $configure
     */
    private function run(
        PdfTool $tool,
        array $files,
        string $outputDir,
        ?string $outputFilename,
        ?callable $configure = null,
    ): PdfTaskResult {
        $this->ensureCredentials();

        $files = array_values($files);
        if ($files === []) {
            throw PdfProcessingException::emptyFileList($tool->value);
        }

        foreach ($files as $path) {
            if (! is_file($path)) {
                throw PdfProcessingException::fileNotFound($path);
            }
        }

        $resolvedDir = $this->prepareOutputDirectory($outputDir);

        $client = new Ilovepdf($this->publicKey, $this->secretKey);
        $task = $client->newTask($tool->value);

        $this->applyDefaults($task);

        if ($configure !== null) {
            $configure($task);
        }

        if ($outputFilename !== null && $outputFilename !== '') {
            $cleanName = $this->stripExtension($outputFilename);
            $task->setOutputFilename($cleanName);
            $task->setPackagedFilename($cleanName);
        }

        try {
            foreach ($files as $path) {
                $task->addFile($path);
            }

            $task->execute();
            $task->download($resolvedDir);
        } catch (Throwable $e) {
            throw PdfProcessingException::fromTool($tool->value, $e);
        }

        $producedName = $task->outputFileName ?? null;
        $producedType = $task->outputFileType ?? null;

        if (! is_string($producedName) || $producedName === '') {
            throw new PdfProcessingException(
                sprintf('iLovePDF "%s" task completed but no output filename was reported.', $tool->value)
            );
        }

        $absolute = rtrim($resolvedDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$producedName;
        $isArchive = strtolower((string) $producedType) === 'zip';
        $size = is_file($absolute) ? (int) filesize($absolute) : 0;

        return new PdfTaskResult(
            tool: $tool->value,
            taskId: is_string($task->task) ? $task->task : null,
            path: $absolute,
            filename: $producedName,
            mimeType: $isArchive ? 'application/zip' : 'application/pdf',
            size: $size,
            isArchive: $isArchive,
        );
    }

    private function ensureCredentials(): void
    {
        if (! is_string($this->publicKey) || $this->publicKey === ''
            || ! is_string($this->secretKey) || $this->secretKey === ''
        ) {
            throw PdfConfigurationException::missingCredentials();
        }
    }

    private function applyDefaults(Task $task): void
    {
        $task->setIgnoreErrors((bool) ($this->defaults['ignore_errors'] ?? true));
        $task->setIgnorePassword((bool) ($this->defaults['ignore_password'] ?? true));
        $task->setTryPdfRepair((bool) ($this->defaults['try_pdf_repair'] ?? true));
    }

    private function prepareOutputDirectory(string $path): string
    {
        if ($path === '') {
            throw PdfProcessingException::invalidOutputDirectory($path);
        }

        if (! is_dir($path) && ! mkdir($path, 0775, true) && ! is_dir($path)) {
            throw PdfProcessingException::invalidOutputDirectory($path);
        }

        if (! is_writable($path)) {
            throw PdfProcessingException::invalidOutputDirectory($path);
        }

        return $path;
    }

    private function stripExtension(string $filename): string
    {
        $info = pathinfo($filename);

        return $info['filename'] ?? $filename;
    }
}
