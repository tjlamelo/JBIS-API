<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Services\Document\DocumentStorageService;

final class DeleteUserDocumentAction
{
    public function __construct(
        private readonly DocumentStorageService $storage,
    ) {}

    public function execute(UserDocument $document): void
    {
        $this->storage->delete($document->file_path);
        $document->delete();
    }
}
