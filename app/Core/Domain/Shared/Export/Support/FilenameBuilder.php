<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Support;

use App\Core\Domain\Shared\Export\Enums\ExportFormat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Construit des chemins de fichiers temporaires sûrs pour les exports.
 *
 *  - Slugifie le nom de fichier souhaité
 *  - Ajoute un horodatage et un identifiant unique court (anti-collision)
 *  - Garantit que le dossier de destination existe (storage/app/exports)
 */
final class FilenameBuilder
{
    /**
     * @return array{absolute_path: string, download_name: string}
     */
    public function build(string $desired, ExportFormat $format): array
    {
        $slug = Str::slug($desired) ?: 'export';
        $slug = Str::limit($slug, 80, '');
        $timestamp = Carbon::now()->format('Ymd-His');
        $rand = Str::lower(Str::random(6));

        $extension = $format->extension();
        $downloadName = sprintf('%s-%s.%s', $slug, $timestamp, $extension);

        $directory = storage_path('app/exports');
        if (! is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $absolute = $directory.DIRECTORY_SEPARATOR.sprintf('%s-%s.%s', $slug, $rand, $extension);

        return [
            'absolute_path' => $absolute,
            'download_name' => $downloadName,
        ];
    }
}
