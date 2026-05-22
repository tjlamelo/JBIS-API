<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\DTOs\ProcessFlow;

/**
 * @phpstan-type StepPrint array{
 *   order: int,
 *   title: string,
 *   description: string|null,
 *   step_type_label: string,
 *   amount: string|null,
 *   requires_documents: bool,
 *   document_labels: list<string>,
 * }
 * @phpstan-type SectionPrint array{
 *   order: int,
 *   key: string,
 *   title: string,
 *   description: string|null,
 *   color: string,
 *   icon: string|null,
 *   visible_after: string|null,
 *   steps: list<StepPrint>,
 * }
 */
final readonly class ProcessFlowPrintViewModel
{
    /**
     * @param  list<SectionPrint>  $sections
     */
    public function __construct(
        public string $locale,
        public string $title,
        public string $statusLabel,
        public int $version,
        public string $generatedAt,
        public ?string $programLabel,
        public ?string $offerLabel,
        public ?string $countryLabel,
        public int $stepsCount,
        public ?string $fileOpeningFeeLabel,
        public ?string $totalProcedureFeesLabel,
        public array $sections,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'locale' => $this->locale,
            'title' => $this->title,
            'statusLabel' => $this->statusLabel,
            'version' => $this->version,
            'generatedAt' => $this->generatedAt,
            'programLabel' => $this->programLabel,
            'offerLabel' => $this->offerLabel,
            'countryLabel' => $this->countryLabel,
            'stepsCount' => $this->stepsCount,
            'fileOpeningFeeLabel' => $this->fileOpeningFeeLabel,
            'totalProcedureFeesLabel' => $this->totalProcedureFeesLabel,
            'sections' => $this->sections,
        ];
    }
}
