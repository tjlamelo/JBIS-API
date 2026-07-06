<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\DTOs;

final readonly class ProcessFlowImportResult
{
    /**
     * @param  list<ProcessFlowImportIssue>  $issues
     * @param  list<ProcessFlowImportIssue>  $warnings
     * @param  list<ProcessFlowImportFlowSummary>  $summaries
     * @param  list<int>  $createdFlowIds
     */
    public function __construct(
        public bool $committed,
        public bool $success,
        public array $issues,
        public array $warnings,
        public array $summaries,
        public array $createdFlowIds,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'committed' => $this->committed,
            'success' => $this->success,
            'issues' => array_map(static fn (ProcessFlowImportIssue $issue): array => $issue->toArray(), $this->issues),
            'warnings' => array_map(static fn (ProcessFlowImportIssue $warning): array => $warning->toArray(), $this->warnings),
            'summaries' => array_map(static fn (ProcessFlowImportFlowSummary $summary): array => $summary->toArray(), $this->summaries),
            'created_flow_ids' => $this->createdFlowIds,
        ];
    }
}
