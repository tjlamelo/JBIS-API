<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Sources;

use App\Core\Domain\Identity\Models\Archive;
use App\Core\Domain\Shared\Pdf\Exceptions\PdfProcessingException;

/**
 * Source représentant un document d'archive de l'entreprise.
 *
 * Le model `Archive` porte `disk` + `stored_name` ; on délègue la matérialisation
 * à un `StorageDiskSource`. La sémantique du wrapper rend l'appel auto-documenté
 * côté business :
 *
 *     ArchiveSource::of($archive);
 */
final class ArchiveSource implements PdfSource
{
    private readonly StorageDiskSource $inner;

    public function __construct(private readonly Archive $archive)
    {
        $disk = (string) ($archive->disk ?: 'jbis_assets');
        $path = (string) ($archive->stored_name ?: '');

        if ($path === '') {
            throw new PdfProcessingException(
                sprintf('Archive #%s has no stored_name.', (string) $archive->getKey())
            );
        }

        $this->inner = new StorageDiskSource($disk, $path);
    }

    public static function of(Archive $archive): self
    {
        return new self($archive);
    }

    public function materialize(): string
    {
        return $this->inner->materialize();
    }

    public function cleanup(): void
    {
        $this->inner->cleanup();
    }

    public function originalName(): string
    {
        return (string) ($this->archive->original_name ?: $this->inner->originalName());
    }

    public function archive(): Archive
    {
        return $this->archive;
    }
}
