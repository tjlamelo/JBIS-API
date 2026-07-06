<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\DTOs;

final readonly class ProcessFlowImportFlowSummary
{
    public function __construct(
        public string $flowKey,
        public string $flowGroupId,
        public int $version,
        public bool $isNewGroup,
        public int $sectionsCount,
        public int $stepsCount,
        public int $documentsCount,
        public string $status,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'flow_key' => $this->flowKey,
            'flow_group_id' => $this->flowGroupId,
            'version' => $this->version,
            'is_new_group' => $this->isNewGroup,
            'sections_count' => $this->sectionsCount,
            'steps_count' => $this->stepsCount,
            'documents_count' => $this->documentsCount,
            'status' => $this->status,
        ];
    }
}
