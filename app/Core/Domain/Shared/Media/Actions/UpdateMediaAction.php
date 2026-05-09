<?php
namespace App\Core\Domain\Shared\Media\Actions;

use App\Core\Domain\Shared\Media\DTOs\UploadedMediaDto;
use Illuminate\Http\UploadedFile;

class UpdateMediaAction
{
    public function __construct(
        private readonly StoreMediaAction $storeAction,
        private readonly DeleteMediaAction $deleteAction
    ) {}

    public function execute(
        UploadedFile $newFile, 
        string $oldOptimizedPath,
        string $oldRawPath,
        ?string $oldCloudinaryId, 
        string $folder = 'uploads'
    ): UploadedMediaDto {
        // Idempotence : On nettoie avant de remplacer
        $this->deleteAction->execute($oldOptimizedPath, $oldRawPath, $oldCloudinaryId);

        return $this->storeAction->execute($newFile, $folder);
    }
}