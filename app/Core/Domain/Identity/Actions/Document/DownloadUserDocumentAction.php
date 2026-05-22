<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Exceptions\Document\DocumentStorageException;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Support\Document\DocumentDownloadNameBuilder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DownloadUserDocumentAction
{
    public function __construct(
        private readonly DocumentDownloadNameBuilder $downloadNames,
    ) {}

    public function execute(UserDocument $document): StreamedResponse
    {
        $document->loadMissing(['user.profile']);

        $disk = Storage::disk(UserDocument::STORAGE_DISK);
        $path = (string) $document->file_path;

        if ($path === '' || ! $disk->exists($path)) {
            throw new DocumentStorageException(__('Fichier document introuvable.'));
        }

        $downloadName = $this->downloadNames->forDocument($document);

        return $disk->download($path, $downloadName);
    }
}
