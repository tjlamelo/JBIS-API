<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

use App\Core\Domain\Identity\DTOs\Document\StoredDocumentFileDto;
use App\Core\Domain\Identity\Exceptions\Document\DocumentStorageException;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Support\Document\DocumentFilenameBuilder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stockage des pièces utilisateur sur {@see UserDocument::STORAGE_DISK} (assets.jbis.cm). Sans Cloudinary.
 */
final class DocumentStorageService
{
    public function __construct(
        private readonly DocumentFilenameBuilder $filenameBuilder,
    ) {}

    public function store(UploadedFile $file, int $userId, DocumentType $type): StoredDocumentFileDto
    {
        $disk = Storage::disk(UserDocument::STORAGE_DISK);
        $built = $this->filenameBuilder->build($file, $userId, $type);
        $targetFolder = trim(UserDocument::storageFolderForUser($userId).'/'.now()->format('Y/m'), '/');
        $fileName = "{$built['storage_base']}.{$built['extension']}";
        $relativePath = "{$targetFolder}/{$fileName}";

        if (! $disk->putFileAs($targetFolder, $file, $fileName)) {
            throw new DocumentStorageException('Impossible d\'enregistrer le fichier sur le disque assets.');
        }

        $publicUrl = rtrim((string) config('filesystems.disks.'.UserDocument::STORAGE_DISK.'.url'), '/')
            .'/'.ltrim($relativePath, '/');

        return new StoredDocumentFileDto(
            filePath: $relativePath,
            publicUrl: $publicUrl,
            originalFilename: $built['display_filename'],
            mimeType: (string) (Storage::disk(UserDocument::STORAGE_DISK)->mimeType($relativePath) ?? $file->getMimeType() ?? 'application/octet-stream'),
            fileSize: (int) $file->getSize(),
        );
    }

    public function delete(?string $filePath): void
    {
        if ($filePath === null || $filePath === '') {
            return;
        }

        $disk = Storage::disk(UserDocument::STORAGE_DISK);
        if ($disk->exists($filePath)) {
            $disk->delete($filePath);
        }
    }

    public function publicUrl(?string $filePath): ?string
    {
        if ($filePath === null || $filePath === '') {
            return null;
        }

        return Storage::disk(UserDocument::STORAGE_DISK)->url($filePath);
    }
}
