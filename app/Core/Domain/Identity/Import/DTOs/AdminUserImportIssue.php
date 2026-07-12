<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import\DTOs;

final class AdminUserImportIssue
{
    public function __construct(
        public readonly string $path,
        public readonly string $field,
        public readonly string $message,
        public readonly string $severity = 'error',
    ) {}

    /**
     * @return array{path: string, field: string, message: string, severity: string}
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'field' => $this->field,
            'message' => $this->message,
            'severity' => $this->severity,
        ];
    }
}
