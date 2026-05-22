<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Media\Drivers;

use App\Core\Domain\Shared\Media\Contracts\MediaStorageDriverInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class LocalMirrorStorageDriver implements MediaStorageDriverInterface
{
    public function store(UploadedFile $file, string $targetFolder, string $baseName): array
    {
        $extension = $file->getClientOriginalExtension();

        $mimeType = (string) ($file->getMimeType() ?? '');
        $isImage = str_starts_with($mimeType, 'image/');

        $localRawPath = "{$targetFolder}/raw/{$baseName}.{$extension}";
        Storage::disk('jbis_assets')->putFileAs("{$targetFolder}/raw", $file, "{$baseName}.{$extension}");

        $localOptimizedPath = '';
        if ($isImage) {
            $localOptimizedPath = "{$targetFolder}/optimized/{$baseName}.webp";
            $optimizedImage = Image::read($file)
                ->scale(width: 1200)
                ->toWebp(80);
            Storage::disk('jbis_assets')->put($localOptimizedPath, (string) $optimizedImage);
        }

        $publicPath = $localOptimizedPath !== '' ? $localOptimizedPath : $localRawPath;
        $publicUrl = rtrim((string) config('filesystems.disks.jbis_assets.url'), '/').'/'.$publicPath;

        return [
            'local_optimized_path' => $localOptimizedPath,
            'local_raw_path' => $localRawPath,
            'public_url' => $publicUrl,
        ];
    }

    public function delete(array $media): void
    {
        $disk = Storage::disk('jbis_assets');
        $optimizedPath = isset($media['local_optimized_path']) ? (string) $media['local_optimized_path'] : null;
        $rawPath = isset($media['local_raw_path']) ? (string) $media['local_raw_path'] : null;

        if ($optimizedPath && $disk->exists($optimizedPath)) {
            $disk->delete($optimizedPath);
        }

        if ($rawPath && $disk->exists($rawPath)) {
            $disk->delete($rawPath);
        }
    }
}
