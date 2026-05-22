<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\DTOs;

/**
 * Résultat d'une tâche iLovePDF exécutée.
 *
 *  - `tool`         : identifiant de l'outil (`compress`, `merge`, ...)
 *  - `taskId`       : identifiant côté iLovePDF (utile pour les logs / debug)
 *  - `path`         : chemin absolu du fichier produit (pdf ou zip)
 *  - `filename`     : nom de fichier produit (basename)
 *  - `mimeType`     : mime type détecté (`application/pdf` ou `application/zip`)
 *  - `size`         : taille en octets
 *  - `isArchive`    : vrai si plusieurs fichiers ont été archivés en ZIP
 */
final class PdfTaskResult
{
    public function __construct(
        public readonly string $tool,
        public readonly ?string $taskId,
        public readonly string $path,
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly bool $isArchive,
    ) {}

    public function toArray(): array
    {
        return [
            'tool' => $this->tool,
            'task_id' => $this->taskId,
            'path' => $this->path,
            'filename' => $this->filename,
            'mime_type' => $this->mimeType,
            'size' => $this->size,
            'is_archive' => $this->isArchive,
        ];
    }
}
