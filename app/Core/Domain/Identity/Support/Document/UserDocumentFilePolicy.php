<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support\Document;

use App\Core\Domain\Identity\Models\DocumentType;
use Illuminate\Http\UploadedFile;

/**
 * Politique commune : PDF, images et documents Word (extension + MIME réel).
 */
final class UserDocumentFilePolicy
{
    public const MAX_SIZE_KB_DEFAULT = 10240;

    public const MAX_SIZE_KB_PHOTO = 5120;

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];

    /** @var list<string> */
    public const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /** @var array<string, list<string>> */
    private const MIME_TO_EXTENSIONS = [
        'application/pdf' => ['pdf'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
    ];

    public static function maxSizeKbFor(DocumentType $type): int
    {
        return $type->max_file_size_kb > 0
            ? $type->max_file_size_kb
            : self::MAX_SIZE_KB_DEFAULT;
    }

    public static function allowedExtensionsLabel(): string
    {
        return 'pdf, jpg, jpeg, png, webp, doc, docx';
    }

    public static function validate(UploadedFile $file, DocumentType $type): ?string
    {
        $maxKb = self::maxSizeKbFor($type);
        $allowedExtensions = $type->allowedExtensionsList();

        if ($file->getSize() > $maxKb * 1024) {
            return __('Fichier trop volumineux pour :type (max :max Ko).', [
                'type' => $type->label,
                'max' => $maxKb,
            ]);
        }

        $extension = self::normalizeExtension($file->getClientOriginalExtension() ?: '');

        if ($extension === '' || ! in_array($extension, $allowedExtensions, true)) {
            return __('Le fichier doit être un PDF, une image (JPG, PNG, WEBP) ou un document Word (DOC, DOCX).');
        }

        $detectedMime = self::detectMimeType($file, $extension);
        $allowedMimes = $type->allowedMimeTypesList();

        if ($detectedMime === null || ! in_array($detectedMime, $allowedMimes, true)) {
            return __('Le fichier doit être un PDF, une image (JPG, PNG, WEBP) ou un document Word (DOC, DOCX).');
        }

        $allowedForMime = self::MIME_TO_EXTENSIONS[$detectedMime] ?? [];

        if ($allowedForMime !== [] && ! in_array($extension, $allowedForMime, true)) {
            return __('Le type du fichier ne correspond pas à son extension.');
        }

        return null;
    }

    public static function resolveExtension(UploadedFile $file): string
    {
        $extension = self::normalizeExtension($file->getClientOriginalExtension() ?: '');
        $mime = self::detectMimeType($file, $extension);

        if ($mime !== null && isset(self::MIME_TO_EXTENSIONS[$mime])) {
            $allowed = self::MIME_TO_EXTENSIONS[$mime];

            if (in_array($extension, $allowed, true)) {
                return $extension === 'jpeg' ? 'jpg' : $extension;
            }

            return $allowed[0] === 'jpeg' ? 'jpg' : $allowed[0];
        }

        return $extension === 'jpeg' ? 'jpg' : $extension;
    }

    private static function normalizeExtension(string $extension): string
    {
        return strtolower(trim($extension));
    }

    private static function detectMimeType(UploadedFile $file, string $extension = ''): ?string
    {
        $path = $file->getRealPath();

        if (is_string($path) && $path !== '' && is_file($path)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $fromFile = finfo_file($finfo, $path);
                finfo_close($finfo);

                if (is_string($fromFile) && $fromFile !== '') {
                    return self::normalizeMime($fromFile, $extension);
                }
            }
        }

        $guessed = $file->getMimeType();

        return is_string($guessed) && $guessed !== ''
            ? self::normalizeMime($guessed, $extension)
            : null;
    }

    private static function normalizeMime(string $mime, string $extension = ''): string
    {
        $normalized = strtolower(trim(explode(';', $mime)[0]));

        if ($extension === 'docx' && $normalized === 'application/zip') {
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }

        if ($extension === 'doc' && in_array($normalized, ['application/CDFV2', 'application/x-cfb'], true)) {
            return 'application/msword';
        }

        return $normalized;
    }
}
