<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\DTOs;

final readonly class ExportResultDto
{
    public function __construct(
        public string $absolutePath,
        public string $downloadFileName,
        public string $mimeType,
    ) {}
}
