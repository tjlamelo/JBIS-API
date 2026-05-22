<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support\Document;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Services\Document\DocumentTypeResolver;

final class DocumentDownloadNameBuilder
{
    public function __construct(
        private readonly DocumentTypeResolver $documentTypeResolver,
    ) {}

    public function forDocument(UserDocument $document): string
    {
        return DocumentFilenameHelper::buildDownloadName(
            $document,
            $this->resolveType($document),
        );
    }

    public function forZipArchive(User $user, int $documentCount): string
    {
        $user->loadMissing('profile');

        $profile = $user->profile;
        $parts = array_filter([
            DocumentFilenameHelper::slugSegment($profile?->last_name, 'nom'),
            DocumentFilenameHelper::slugSegment($profile?->first_name, 'prenom'),
            'documents',
            now()->format('Y-m-d'),
            (string) $documentCount,
        ]);

        return implode('_', $parts).'.zip';
    }

    public function forDocumentInZip(UserDocument $document, int $index): string
    {
        $base = pathinfo($this->forDocument($document), PATHINFO_FILENAME);
        $extension = DocumentFilenameHelper::extensionFromDocument($document);

        if ($index <= 1) {
            return "{$base}.{$extension}";
        }

        return "{$base}_{$index}.{$extension}";
    }

    private function resolveType(UserDocument $document): DocumentType
    {
        if ($document->relationLoaded('documentType') && $document->documentType instanceof DocumentType) {
            return $document->documentType;
        }

        return $this->documentTypeResolver->resolveById((int) $document->document_type_id);
    }
}
