<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\DTOs;

final readonly class ProcessFlowImportFlowData
{
    /**
     * @param  array{fr: string, en: string}  $name
     * @param  list<ProcessFlowImportSectionData>  $sections
     */
    public function __construct(
        public string $flowKey,
        public string $countryCode,
        public array $name,
        public ?float $fileOpeningFee,
        public ?float $totalProcedureFees,
        public ?int $estimatedDurationDays,
        public array $sections,
    ) {}
}
