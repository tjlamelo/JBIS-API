<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Sources;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Shared\Pdf\Exceptions\PdfProcessingException;

/**
 * Source représentant un `UserDocument` (pièce unique, sans recto/verso).
 *
 * Les pièces utilisateurs résident sur le disque `jbis_assets`, sous le dossier
 * `Identity/` (URL publique : `https://assets.jbis.cm/Identity/...`).
 *
 *     UserDocumentSource::of($userDocument);
 */
final class UserDocumentSource implements PdfSource
{
    private readonly StorageDiskSource $inner;

    public function __construct(
        private readonly UserDocument $document,
        string $disk = UserDocument::STORAGE_DISK,
    ) {
        $path = (string) ($document->file_path ?? '');

        if ($path === '') {
            throw new PdfProcessingException(
                sprintf('UserDocument #%s has no file_path.', (string) $document->getKey())
            );
        }

        $this->inner = new StorageDiskSource($disk, $path);
    }

    public static function of(UserDocument $document, string $disk = UserDocument::STORAGE_DISK): self
    {
        return new self($document, $disk);
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
        return $this->inner->originalName();
    }

    public function document(): UserDocument
    {
        return $this->document;
    }
}
