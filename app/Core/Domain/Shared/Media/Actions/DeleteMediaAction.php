<?php
namespace App\Core\Domain\Shared\Media\Actions;

use App\Core\Domain\Shared\Media\Drivers\CloudinaryStorageDriver;
use App\Core\Domain\Shared\Media\Drivers\LocalMirrorStorageDriver;

class DeleteMediaAction
{
    public function __construct(
        private readonly LocalMirrorStorageDriver $localDriver,
        private readonly CloudinaryStorageDriver $cloudinaryDriver,
    ) {
    }

    public function execute(string $optimizedPath, string $rawPath, ?string $cloudinaryId = null): bool
    {
        $media = [
            'local_optimized_path' => $optimizedPath,
            'local_raw_path' => $rawPath,
            'cloudinary_id' => $cloudinaryId,
        ];
        $this->localDriver->delete($media);
        $this->cloudinaryDriver->delete($media);

        return true;
    }
}