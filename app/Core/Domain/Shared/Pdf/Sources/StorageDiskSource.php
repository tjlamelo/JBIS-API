<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Sources;

use App\Core\Domain\Shared\Pdf\Exceptions\PdfProcessingException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Source basée sur un disque Laravel.
 *
 *  - Si le disque est local (driver `local`), `materialize()` retourne directement
 *    le chemin physique (`$disk->path($relative)`), sans copie ni tmp.
 *  - Sinon (s3, ftp, ...), le contenu est téléchargé dans un fichier temporaire
 *    qui sera supprimé par `cleanup()`.
 *
 * Usage typique :
 *
 *     StorageDiskSource::on('jbis_assets', $userDocument->file_path);
 *     StorageDiskSource::on($archive->disk, $archive->stored_name);
 */
final class StorageDiskSource implements PdfSource
{
    private ?string $temporaryPath = null;

    public function __construct(
        private readonly string $disk,
        private readonly string $relativePath,
    ) {}

    public static function on(string $disk, string $relativePath): self
    {
        return new self($disk, $relativePath);
    }

    public function materialize(): string
    {
        $filesystem = $this->disk();

        if (! $filesystem->exists($this->relativePath)) {
            throw PdfProcessingException::fileNotFound(
                sprintf('%s://%s', $this->disk, $this->relativePath)
            );
        }

        if ($filesystem instanceof FilesystemAdapter) {
            try {
                $absolute = $filesystem->path($this->relativePath);
                if (is_file($absolute)) {
                    return $absolute;
                }
            } catch (\Throwable) {
                // Le driver ne supporte pas `path()` → on retombe sur la copie tmp.
            }
        }

        return $this->downloadToTemporary($filesystem);
    }

    public function cleanup(): void
    {
        if ($this->temporaryPath !== null && is_file($this->temporaryPath)) {
            @unlink($this->temporaryPath);
        }
        $this->temporaryPath = null;
    }

    public function originalName(): string
    {
        return basename($this->relativePath);
    }

    public function disk(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    public function relativePath(): string
    {
        return $this->relativePath;
    }

    public function diskName(): string
    {
        return $this->disk;
    }

    private function downloadToTemporary(Filesystem $filesystem): string
    {
        $tmpDir = storage_path('app/tmp/pdf');
        if (! is_dir($tmpDir) && ! mkdir($tmpDir, 0775, true) && ! is_dir($tmpDir)) {
            throw PdfProcessingException::invalidOutputDirectory($tmpDir);
        }

        $extension = pathinfo($this->relativePath, PATHINFO_EXTENSION) ?: 'pdf';
        $target = $tmpDir.DIRECTORY_SEPARATOR.Str::uuid()->toString().'.'.$extension;

        $stream = $filesystem->readStream($this->relativePath);
        if ($stream === null || $stream === false) {
            throw new PdfProcessingException(
                sprintf('Unable to read "%s" from disk "%s".', $this->relativePath, $this->disk)
            );
        }

        $out = fopen($target, 'wb');
        if ($out === false) {
            throw PdfProcessingException::invalidOutputDirectory($target);
        }

        try {
            stream_copy_to_stream($stream, $out);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            fclose($out);
        }

        $this->temporaryPath = $target;

        return $target;
    }
}
