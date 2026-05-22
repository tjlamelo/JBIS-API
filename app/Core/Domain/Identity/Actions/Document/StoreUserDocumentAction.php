<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\DTOs\Document\UserDocumentDto;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Services\Document\DocumentStorageService;
use Illuminate\Http\UploadedFile;

final class StoreUserDocumentAction
{
    public function __construct(
        private readonly DocumentStorageService $storage,
    ) {}

    public function execute(UserDocumentDto $dto, UploadedFile $file): UserDocument
    {
        $stored = $this->storage->store($file, $dto->userId, $dto->documentType);

        $attributes = array_merge($dto->toAttributes(), [
            'file_path' => $stored->filePath,
            'original_filename' => $stored->originalFilename,
            'mime_type' => $stored->mimeType,
            'file_size' => $stored->fileSize,
        ]);

        return UserDocument::query()->create($attributes);
    }
}
