<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Media\Support;

use Illuminate\Support\Str;

class MediaPathBuilder
{
    /**
     * @return array{target_folder:string,base_name:string}
     */
    public function build(string $folder, ?string $originalName = null): array
    {
        $cleanFolder = $this->normalizeFolder($folder);
        $datePath = now()->format('Y/m');
        $targetFolder = trim($cleanFolder.'/'.$datePath, '/');

        $sourceName = $originalName ?: 'media';
        $nameWithoutExt = pathinfo($sourceName, PATHINFO_FILENAME);
        $safeName = Str::slug((string) $nameWithoutExt);
        if ($safeName === '') {
            $safeName = 'media';
        }

        // Cloudinary public_id length constraints: keep base name short even for very long filenames.
        if (Str::length($safeName) > 80) {
            $safeName = Str::substr($safeName, 0, 60).'-'.Str::lower(Str::substr(md5($safeName), 0, 10));
        }

        $baseName = sprintf(
            '%s_%s_%s',
            $safeName,
            now()->format('YmdHis'),
            Str::lower(Str::random(8))
        );

        return [
            'target_folder' => $targetFolder,
            'base_name' => $baseName,
        ];
    }

    private function normalizeFolder(string $folder): string
    {
        $segments = array_values(array_filter(
            explode('/', str_replace('\\', '/', trim($folder))),
            static fn (string $segment): bool => trim($segment) !== ''
        ));

        if ($segments === []) {
            return 'shared/uploads';
        }

        $cleaned = array_map(
            static fn (string $segment): string => Str::slug($segment),
            $segments
        );

        return implode('/', array_filter($cleaned));
    }
}
