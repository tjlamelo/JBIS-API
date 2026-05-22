<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Media\Drivers;

use App\Core\Domain\Shared\Media\Contracts\MediaStorageDriverInterface;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryStorageDriver implements MediaStorageDriverInterface
{
    public function store(UploadedFile $file, string $targetFolder, string $baseName): array
    {
        $mimeType = (string) ($file->getMimeType() ?? '');
        if (! str_starts_with($mimeType, 'image/')) {
            return [];
        }

        if (config('app.debug')) {
            Log::debug('Cloudinary upload attempt', [
                'cloudinary_config_keys' => array_keys((array) config('cloudinary', [])),
                'has_cloud_config' => is_array(config('cloudinary.cloud')),
                'cloud_url_set' => (string) config('cloudinary.cloud_url', '') !== '',
                'target_folder' => $targetFolder,
                'base_name' => $baseName,
                'mime' => $mimeType,
            ]);
        }

        try {
            $response = Cloudinary::uploadApi()->upload($file->getRealPath(), [
                'folder' => 'jbis.cm/'.$targetFolder,
                'public_id' => $baseName,
            ]);

            $cloudinaryId = isset($response['public_id']) ? (string) $response['public_id'] : null;
            $publicUrl = isset($response['secure_url']) ? (string) $response['secure_url'] : (string) ($response['url'] ?? '');

            if (! $cloudinaryId || $publicUrl === '') {
                return [];
            }

            return [
                'cloudinary_id' => $cloudinaryId,
                'public_url' => $publicUrl,
            ];
        } catch (\Throwable $e) {
            Log::warning('Cloudinary Storage Failover: '.$e->getMessage(), [
                'exception' => get_class($e),
                'has_cloud_config' => is_array(config('cloudinary.cloud')),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [];
        }
    }

    public function delete(array $media): void
    {
        $cloudinaryId = isset($media['cloudinary_id']) ? (string) $media['cloudinary_id'] : null;
        if (! $cloudinaryId) {
            return;
        }

        try {
            Cloudinary::uploadApi()->destroy($cloudinaryId);
        } catch (\Throwable $e) {
            Log::error('Media Cleanup Error [Cloudinary]: '.$cloudinaryId);
        }
    }
}
