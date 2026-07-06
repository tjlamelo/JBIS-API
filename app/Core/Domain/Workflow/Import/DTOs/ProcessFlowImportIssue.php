<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\DTOs;

final readonly class ProcessFlowImportIssue
{
    public function __construct(
        public string $path,
        public string $field,
        public string $message,
        public string $severity = 'error',
    ) {}

    public function isError(): bool
    {
        return $this->severity === 'error';
    }

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
