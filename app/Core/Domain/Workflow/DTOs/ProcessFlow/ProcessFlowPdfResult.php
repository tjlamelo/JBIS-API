<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\DTOs\ProcessFlow;

final readonly class ProcessFlowPdfResult
{
    public function __construct(
        public string $absolutePath,
        public string $downloadFileName,
        public string $mimeType = 'application/pdf',
    ) {}
}
