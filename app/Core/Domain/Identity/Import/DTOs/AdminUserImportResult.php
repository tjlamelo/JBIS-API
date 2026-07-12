<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import\DTOs;

final class AdminUserImportResult
{
    /**
     * @param  list<AdminUserImportIssue>  $issues
     * @param  list<array{line: int, email: string}>  $rows
     * @param  list<int>  $createdUserIds
     */
    public function __construct(
        public readonly bool $success,
        public readonly bool $committed,
        public readonly int $validRows,
        public readonly int $createdCount,
        public readonly array $issues,
        public readonly array $rows = [],
        public readonly array $createdUserIds = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'committed' => $this->committed,
            'valid_rows' => $this->validRows,
            'created_count' => $this->createdCount,
            'created_user_ids' => $this->createdUserIds,
            'rows' => $this->rows,
            'issues' => array_map(
                static fn (AdminUserImportIssue $issue): array => $issue->toArray(),
                $this->issues,
            ),
        ];
    }
}
