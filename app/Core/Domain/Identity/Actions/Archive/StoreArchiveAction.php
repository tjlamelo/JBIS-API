<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Archive;

use App\Core\Domain\Identity\Enums\ArchiveCategory;
use App\Core\Domain\Identity\Models\Archive;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class StoreArchiveAction
{
    public function execute(
        User $uploader,
        UploadedFile $file,
        ?string $category = null,
        ?string $description = null,
        ?int $relatedUserId = null,
        bool $isPublic = false,
    ): Archive {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $storedName = sprintf(
            'Archives/%s/%s/%s.%s',
            now()->format('Y'),
            now()->format('m'),
            Str::uuid()->toString(),
            $extension,
        );

        Storage::disk(Archive::STORAGE_DISK)->putFileAs(
            dirname($storedName),
            $file,
            basename($storedName),
        );

        $normalizedCategory = $this->normalizeCategory($category);

        return Archive::query()->create([
            'user_id' => $relatedUserId ?? $uploader->id,
            'uploaded_by' => $uploader->id,
            'related_user_id' => $relatedUserId,
            'original_name' => $file->getClientOriginalName() ?: ('archive.'.$extension),
            'stored_name' => $storedName,
            'file_type' => $this->resolveFileType($mime, $extension),
            'extension' => $extension,
            'mime_type' => $mime,
            'size' => $file->getSize() ?: 0,
            'category' => $normalizedCategory,
            'description' => $description !== null && trim($description) !== '' ? trim($description) : null,
            'disk' => Archive::STORAGE_DISK,
            'is_public' => $isPublic,
        ]);
    }

    private function normalizeCategory(?string $category): string
    {
        $value = strtoupper(trim((string) $category));
        if ($value === '') {
            return ArchiveCategory::Other->value;
        }

        return ArchiveCategory::tryFrom($value)?->value ?? ArchiveCategory::Other->value;
    }

    private function resolveFileType(string $mime, string $extension): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }
        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'], true)) {
            return 'archive';
        }
        if ($extension === 'pdf' || $mime === 'application/pdf') {
            return 'pdf';
        }

        return 'document';
    }
}
