<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\Support;

use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportIssue;

final class ProcessFlowImportForbiddenFields
{
    /** @var list<string> */
    private const FLOW_FORBIDDEN = [
        'id',
        'flow_group_id',
        'version',
        'process_flow_id',
        'status',
        'created_by',
        'imported_by',
        'created_at',
        'updated_at',
    ];

    /** @var list<string> */
    private const SECTION_FORBIDDEN = [
        'id',
        'process_flow_id',
        'created_at',
        'updated_at',
    ];

    /** @var list<string> */
    private const STEP_FORBIDDEN = [
        'id',
        'process_flow_id',
        'process_flow_section_id',
        'document_type_id',
        'document_type_ids',
        'created_at',
        'updated_at',
    ];

    /** @var list<string> */
    private const WARNING_ONLY = ['status'];

    /**
     * @param  array<string, mixed>  $row
     * @return list<ProcessFlowImportIssue>
     */
    public function scanFlowRow(array $row, string $path): array
    {
        return $this->scan($row, $path, self::FLOW_FORBIDDEN);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<ProcessFlowImportIssue>
     */
    public function scanSectionRow(array $row, string $path): array
    {
        return $this->scan($row, $path, self::SECTION_FORBIDDEN);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<ProcessFlowImportIssue>
     */
    public function scanStepRow(array $row, string $path): array
    {
        return $this->scan($row, $path, self::STEP_FORBIDDEN);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<ProcessFlowImportIssue>
     */
    public function scanJsonFlow(array $item, string $path): array
    {
        $issues = $this->scan($item, $path, self::FLOW_FORBIDDEN);

        foreach ($item['sections'] ?? [] as $sectionIndex => $section) {
            if (! is_array($section)) {
                continue;
            }

            $sectionPath = "{$path}.sections[{$sectionIndex}]";
            $issues = array_merge($issues, $this->scan($section, $sectionPath, self::SECTION_FORBIDDEN));

            foreach ($section['steps'] ?? [] as $stepIndex => $step) {
                if (! is_array($step)) {
                    continue;
                }

                $issues = array_merge(
                    $issues,
                    $this->scan($step, "{$sectionPath}.steps[{$stepIndex}]", self::STEP_FORBIDDEN),
                );
            }
        }

        return $issues;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $forbidden
     * @return list<ProcessFlowImportIssue>
     */
    private function scan(array $row, string $path, array $forbidden): array
    {
        $issues = [];

        foreach ($row as $key => $value) {
            $normalized = strtolower((string) $key);
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($normalized, self::WARNING_ONLY, true)) {
                $issues[] = new ProcessFlowImportIssue(
                    "{$path}.{$normalized}",
                    $normalized,
                    __('Le champ « :field » est ignoré — le statut est toujours fixé à « draft » par le système.', ['field' => $normalized]),
                    'warning',
                );
                continue;
            }

            if (in_array($normalized, $forbidden, true)) {
                $issues[] = new ProcessFlowImportIssue(
                    "{$path}.{$normalized}",
                    $normalized,
                    __('Le champ « :field » ne doit pas figurer dans le fichier — il est généré ou résolu automatiquement par le système.', ['field' => $normalized]),
                    'error',
                );
            }
        }

        return $issues;
    }
}
