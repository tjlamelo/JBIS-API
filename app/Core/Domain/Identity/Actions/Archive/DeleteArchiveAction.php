<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Archive;

use App\Core\Domain\Identity\Models\Archive;
use Illuminate\Support\Facades\Storage;

final class DeleteArchiveAction
{
    public function execute(Archive $archive): void
    {
        $disk = (string) ($archive->disk ?: Archive::STORAGE_DISK);
        if ($archive->stored_name && Storage::disk($disk)->exists($archive->stored_name)) {
            Storage::disk($disk)->delete($archive->stored_name);
        }

        $archive->delete();
    }
}
