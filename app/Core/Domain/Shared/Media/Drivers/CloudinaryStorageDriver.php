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
        try {
            $result = Cloudinary::upload($file->getRealPath(), [
                'folder' => 'jbis.cm/' . $targetFolder,
                'public_id' => $baseName,
            ]);
            $cloudinaryId = $result->getPublicId();

            return [
                'cloudinary_id' => $cloudinaryId,
                'public_url' => Cloudinary::getUrl($cloudinaryId),
            ];
        } catch (\Throwable $e) {
            Log::warning('Cloudinary Storage Failover: ' . $e->getMessage());
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
            Cloudinary::destroy($cloudinaryId);
        } catch (\Throwable $e) {
            Log::error('Media Cleanup Error [Cloudinary]: ' . $cloudinaryId);
        }
    }
}

