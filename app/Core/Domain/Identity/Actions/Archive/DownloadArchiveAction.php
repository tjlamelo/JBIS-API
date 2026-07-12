<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Archive;

use App\Core\Domain\Identity\Models\Archive;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DownloadArchiveAction
{
    public function execute(Archive $archive): StreamedResponse
    {
        $disk = (string) ($archive->disk ?: Archive::STORAGE_DISK);

        if (! $archive->stored_name || ! Storage::disk($disk)->exists($archive->stored_name)) {
            throw new RuntimeException('Fichier introuvable.');
        }

        return Storage::disk($disk)->download(
            $archive->stored_name,
            $archive->original_name ?: basename($archive->stored_name),
        );
    }
}
