<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Media\Contracts;

use Illuminate\Http\UploadedFile;

interface MediaStorageDriverInterface
{
    /**
     * @return array<string,mixed>
     */
    public function store(UploadedFile $file, string $targetFolder, string $baseName): array;

    /**
     * @param array<string,mixed> $media
     */
    public function delete(array $media): void;
}

