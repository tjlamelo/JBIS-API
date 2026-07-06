<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import;

use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportContext;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportIssue;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportResult;
use App\Core\Domain\Workflow\Import\Readers\ProcessFlowExcelReader;
use App\Core\Domain\Workflow\Import\Readers\ProcessFlowJsonReader;
use App\Core\Domain\Workflow\Import\Support\ProcessFlowImportPayloadNormalizer;
use InvalidArgumentException;

final class ProcessFlowImportCoordinator
{
    public function __construct(
        private readonly ProcessFlowExcelReader $excelReader,
        private readonly ProcessFlowJsonReader $jsonReader,
        private readonly ProcessFlowImportPayloadNormalizer $normalizer,
        private readonly ProcessFlowImportService $importService,
    ) {}

    public function importFromFile(
        string $filePath,
        string $format,
        bool $commit = false,
        ProcessFlowImportContext $context = new ProcessFlowImportContext(),
    ): ProcessFlowImportResult {
        $format = strtolower(trim($format));

        $read = match ($format) {
            'excel', 'xlsx' => $this->excelReader->read($filePath),
            'json' => $this->jsonReader->read($filePath),
            default => throw new InvalidArgumentException(__('Format d\'import non supporté : :format', ['format' => $format])),
        };

        [$errors, $warnings] = $this->partitionIssues($read['issues']);

        if ($errors !== []) {
            return new ProcessFlowImportResult(
                committed: $commit,
                success: false,
                issues: $errors,
                warnings: $warnings,
                summaries: [],
                createdFlowIds: [],
            );
        }

        if ($read['payload'] === null) {
            return new ProcessFlowImportResult(
                committed: $commit,
                success: false,
                issues: [new ProcessFlowImportIssue('$', 'file', __('Fichier vide ou illisible.'))],
                warnings: $warnings,
                summaries: [],
                createdFlowIds: [],
            );
        }

        $payload = $this->normalizer->normalize($read['payload']);

        return $this->importService->import($payload, $commit, $context, $warnings);
    }

    /**
     * @param  list<ProcessFlowImportIssue>  $issues
     * @return array{0: list<ProcessFlowImportIssue>, 1: list<ProcessFlowImportIssue>}
     */
    private function partitionIssues(array $issues): array
    {
        $errors = [];
        $warnings = [];

        foreach ($issues as $issue) {
            if ($issue->isError()) {
                $errors[] = $issue;
            } else {
                $warnings[] = $issue;
            }
        }

        return [$errors, $warnings];
    }
}
