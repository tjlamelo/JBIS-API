<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support\Document;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\UserDocument;
use Illuminate\Support\Str;

final class DocumentFilenameHelper
{
    public static function slugSegment(?string $value, string $fallback = ''): string
    {
        $slug = Str::slug(trim((string) $value));

        return $slug !== '' ? $slug : $fallback;
    }

    public static function normalizeExtension(string $extension): string
    {
        $normalized = strtolower(trim($extension));

        return $normalized === 'jpeg' ? 'jpg' : $normalized;
    }

    public static function extensionFromDocument(UserDocument $document): string
    {
        $fromPath = strtolower(pathinfo((string) $document->file_path, PATHINFO_EXTENSION));
        if ($fromPath !== '') {
            return self::normalizeExtension($fromPath);
        }

        $fromOriginal = strtolower(pathinfo((string) $document->original_filename, PATHINFO_EXTENSION));
        if ($fromOriginal !== '') {
            return self::normalizeExtension($fromOriginal);
        }

        return 'bin';
    }

    /** Nom lisible en stockage / liste (sans espaces). Ex. passeport_2026-05-16.pdf */
    public static function buildStoredDisplayName(
        DocumentType $type,
        string $extension,
        string $date,
        int $sequence = 1,
    ): string {
        $parts = [$type->storage_slug, $date];

        if ($sequence > 1) {
            $parts[] = (string) $sequence;
        }

        return implode('_', $parts).'.'.self::normalizeExtension($extension);
    }

    /** Nom de téléchargement. Ex. dupont_jean_passeport_2026-05-16_42.pdf */
    public static function buildDownloadName(UserDocument $document, DocumentType $type): string
    {
        $document->loadMissing(['user.profile']);

        $profile = $document->user?->profile;
        $extension = self::extensionFromDocument($document);
        $date = $document->created_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        $parts = array_filter([
            self::slugSegment($profile?->last_name),
            self::slugSegment($profile?->first_name),
            $type->storage_slug,
            $date,
            (string) $document->id,
        ]);

        if (count($parts) < 3) {
            $parts = array_filter([
                $type->storage_slug,
                $date,
                (string) $document->id,
            ]);
        }

        return implode('_', $parts).'.'.$extension;
    }
}
