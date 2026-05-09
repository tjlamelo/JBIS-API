<?php

namespace App\Core\Domain\Shared\Media\Actions;

use App\Core\Domain\Shared\Media\DTOs\UploadedMediaDto;
use App\Core\Domain\Shared\Media\Drivers\CloudinaryStorageDriver;
use App\Core\Domain\Shared\Media\Drivers\LocalMirrorStorageDriver;
use App\Core\Domain\Shared\Media\Support\MediaPathBuilder;
use Illuminate\Http\UploadedFile;

class StoreMediaAction
{
    public function __construct(
        private readonly LocalMirrorStorageDriver $localDriver,
        private readonly CloudinaryStorageDriver $cloudinaryDriver,
        private readonly MediaPathBuilder $pathBuilder,
    ) {
    }

    public function execute(UploadedFile $file, string $folder = 'uploads'): UploadedMediaDto
    {
        $path = $this->pathBuilder->build($folder, $file->getClientOriginalName());
        $targetFolder = $path['target_folder'];
        $baseName = $path['base_name'];
        $localResult = $this->localDriver->store($file, $targetFolder, $baseName);
        $cloudResult = $this->cloudinaryDriver->store($file, $targetFolder, $baseName);

        $localOptimizedPath = (string) ($localResult['local_optimized_path'] ?? '');
        $localRawPath = (string) ($localResult['local_raw_path'] ?? '');
        $cloudinaryId = isset($cloudResult['cloudinary_id']) ? (string) $cloudResult['cloudinary_id'] : null;

        // Priorité Cloudinary (CDN), sinon fallback local.
        $finalUrl = (string) ($cloudResult['public_url'] ?? $localResult['public_url'] ?? '');

        return new UploadedMediaDto(
            fileName: $baseName,
            localOptimizedPath: $localOptimizedPath,
            localRawPath: $localRawPath,
            cloudinaryId: $cloudinaryId,
            publicUrl: $finalUrl
        );
    }
}