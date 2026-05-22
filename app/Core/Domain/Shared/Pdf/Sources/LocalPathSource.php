<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Sources;

use App\Core\Domain\Shared\Pdf\Exceptions\PdfProcessingException;

/**
 * Source `PdfSource` à partir d'un chemin filesystem absolu existant.
 *
 * Utilisée pour les fichiers déjà présents sur le serveur (par ex.
 * résolus via `Storage::disk('jbis_assets')->path($rel)`).
 */
final class LocalPathSource implements PdfSource
{
    public function __construct(
        private readonly string $absolutePath,
    ) {}

    public function name(): string
    {
        $info = pathinfo($this->absolutePath);

        return $info['filename'] ?? 'document';
    }

    public function extension(): string
    {
        return strtolower((string) pathinfo($this->absolutePath, PATHINFO_EXTENSION));
    }

    public function materialize(): array
    {
        if (! is_file($this->absolutePath)) {
            throw PdfProcessingException::fileNotFound($this->absolutePath);
        }

        return [
            'path' => $this->absolutePath,
            'cleanup' => null,
        ];
    }
}
