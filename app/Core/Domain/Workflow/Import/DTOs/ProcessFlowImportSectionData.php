<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\DTOs;

final readonly class ProcessFlowImportSectionData
{
    /**
     * @param  array{fr: string, en: string}  $title
     * @param  list<ProcessFlowImportStepData>  $steps
     */
    public function __construct(
        public string $sectionKey,
        public array $title,
        public int $order,
        public array $steps,
    ) {}
}
