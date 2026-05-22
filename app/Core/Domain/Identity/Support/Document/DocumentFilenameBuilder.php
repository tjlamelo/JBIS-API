<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support\Document;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\UserDocument;
use Illuminate\Http\UploadedFile;

final class DocumentFilenameBuilder
{
    /**
     * @return array{storage_base: string, display_filename: string, extension: string}
     */
    public function build(UploadedFile $file, int $userId, DocumentType $type): array
    {
        $extension = UserDocumentFilePolicy::resolveExtension($file);
        $date = now()->format('Y-m-d');
        $typeSlug = $type->storage_slug;
        $sequence = $this->nextSequence($userId, $type);

        $sequenceSuffix = $sequence > 1 ? "_{$sequence}" : '';
        $storageBase = "{$typeSlug}_{$userId}_{$date}{$sequenceSuffix}";
        $displayFilename = DocumentFilenameHelper::buildStoredDisplayName($type, $extension, $date, $sequence);

        return [
            'storage_base' => $storageBase,
            'display_filename' => $displayFilename,
            'extension' => $extension,
        ];
    }

    private function nextSequence(int $userId, DocumentType $type): int
    {
        if ($type->isUniquePerUser()) {
            return 1;
        }

        return UserDocument::query()
            ->where('user_id', $userId)
            ->where('document_type_id', $type->id)
            ->count() + 1;
    }
}
