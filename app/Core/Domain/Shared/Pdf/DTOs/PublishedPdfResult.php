<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\DTOs;

/**
 * Résultat d'une opération PDF de bout en bout (process + publication sur le
 * miroir `jbis_assets`).
 *
 *  - `task`         : détails techniques iLovePDF (id, tool, mime, ...)
 *  - `disk`         : nom du disque Laravel utilisé pour publier le résultat
 *  - `relativePath` : chemin relatif sur ce disque (utilisable directement
 *                     dans `Archive.stored_name` ou `UserDocument.files[*]`)
 *  - `publicUrl`    : URL publique servie par `assets.jbis.cm/...` (ou null
 *                     si le disque ne supporte pas l'URL publique)
 */
final class PublishedPdfResult
{
    public function __construct(
        public readonly PdfTaskResult $task,
        public readonly string $disk,
        public readonly string $relativePath,
        public readonly ?string $publicUrl,
    ) {}

    public function toArray(): array
    {
        return [
            'task' => $this->task->toArray(),
            'disk' => $this->disk,
            'relative_path' => $this->relativePath,
            'public_url' => $this->publicUrl,
        ];
    }
}
