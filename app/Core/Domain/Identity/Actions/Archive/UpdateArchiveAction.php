<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Archive;

use App\Core\Domain\Identity\Enums\ArchiveCategory;
use App\Core\Domain\Identity\Models\Archive;

final class UpdateArchiveAction
{
    /**
     * @param  array{category?: string|null, description?: string|null, is_public?: bool}  $attributes
     */
    public function execute(Archive $archive, array $attributes): Archive
    {
        if (array_key_exists('category', $attributes)) {
            $value = strtoupper(trim((string) ($attributes['category'] ?? '')));
            $archive->category = $value === ''
                ? ArchiveCategory::Other->value
                : (ArchiveCategory::tryFrom($value)?->value ?? ArchiveCategory::Other->value);
        }

        if (array_key_exists('description', $attributes)) {
            $description = $attributes['description'];
            $archive->description = is_string($description) && trim($description) !== ''
                ? trim($description)
                : null;
        }

        if (array_key_exists('is_public', $attributes)) {
            $archive->is_public = (bool) $attributes['is_public'];
        }

        $archive->save();

        return $archive->fresh(['uploader:id,name,email', 'relatedUser:id,name,email']) ?? $archive;
    }
}
