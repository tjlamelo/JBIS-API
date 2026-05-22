<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\DTOs\Document;

final readonly class StoredDocumentFileDto
{
    public function __construct(
        public string $filePath,
        public string $publicUrl,
        public string $originalFilename,
        public string $mimeType,
        public int $fileSize,
    ) {}
}
