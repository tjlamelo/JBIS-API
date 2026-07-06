<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\Readers;

use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportFlowData;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportIssue;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportPayload;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportSectionData;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportStepData;
use App\Core\Domain\Workflow\Import\Support\ProcessFlowImportForbiddenFields;
use App\Core\Domain\Workflow\Import\Support\ProcessFlowImportRowParser;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Lecture Excel (4 feuilles) via phpoffice/phpspreadsheet.
 * maatwebsite/excel n'est pas utilisé : conflit avec phpspreadsheet ^5.7 déjà présent.
 */
final class ProcessFlowExcelReader
{
    public function __construct(
        private readonly ProcessFlowImportForbiddenFields $forbiddenFields,
    ) {}

    /**
     * @return array{payload: ProcessFlowImportPayload|null, issues: list<ProcessFlowImportIssue>}
     */
    public function read(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $issues = [];

        $flowsSheet = $this->resolveSheet($spreadsheet, ['Flows', 'flows']);
        $sectionsSheet = $this->resolveSheet($spreadsheet, ['Sections', 'sections']);
        $stepsSheet = $this->resolveSheet($spreadsheet, ['Steps', 'steps']);
        $documentsSheet = $this->resolveSheet($spreadsheet, ['StepDocuments', 'stepdocuments', 'Step Documents']);

        if ($flowsSheet === null) {
            return [
                'payload' => null,
                'issues' => [new ProcessFlowImportIssue('Flows', 'sheet', __('La feuille « Flows » est introuvable.'))],
            ];
        }

        $flowRows = $this->rowsFromSheet($flowsSheet);
        $sectionRows = $sectionsSheet ? $this->rowsFromSheet($sectionsSheet) : [];
        $stepRows = $stepsSheet ? $this->rowsFromSheet($stepsSheet) : [];
        $documentRows = $documentsSheet ? $this->rowsFromSheet($documentsSheet) : [];

        $flows = [];
        foreach ($flowRows as $rowIndex => $row) {
            $line = $rowIndex + 2;
            $flowKey = ProcessFlowImportRowParser::string($row['flow_key'] ?? null);
            if ($flowKey === '') {
                continue;
            }

            $issues = array_merge($issues, $this->forbiddenFields->scanFlowRow($row, "Flows!A{$line}"));

            $sections = $this->buildSectionsForFlow(
                $flowKey,
                $sectionRows,
                $stepRows,
                $documentRows,
                $issues,
            );

            $flows[] = new ProcessFlowImportFlowData(
                flowKey: $flowKey,
                countryCode: ProcessFlowImportRowParser::string($row['country_code'] ?? null),
                name: [
                    'fr' => ProcessFlowImportRowParser::string($row['name_fr'] ?? null),
                    'en' => ProcessFlowImportRowParser::string($row['name_en'] ?? null),
                ],
                fileOpeningFee: ProcessFlowImportRowParser::nullableFloat($row['file_opening_fee'] ?? null),
                totalProcedureFees: ProcessFlowImportRowParser::nullableFloat($row['total_procedure_fees'] ?? null),
                estimatedDurationDays: ProcessFlowImportRowParser::nullableInt($row['estimated_duration_days'] ?? null),
                sections: $sections,
            );

            if ($sections === [] && $sectionRows !== []) {
                $issues[] = new ProcessFlowImportIssue(
                    "Flows!A{$line}",
                    'flow_key',
                    __('Aucune section trouvée pour le parcours « :key ».', ['key' => $flowKey]),
                );
            }
        }

        if ($flows === []) {
            $issues[] = new ProcessFlowImportIssue('Flows', 'flow_key', __('Aucun parcours valide trouvé dans la feuille Flows.'));
        }

        $issues = array_merge($issues, $this->detectOrphanStepSections($flowRows, $sectionRows, $stepRows));

        return [
            'payload' => $this->hasErrors($issues) ? null : new ProcessFlowImportPayload($flows),
            'issues' => $issues,
        ];
    }

    /**
     * @param  list<ProcessFlowImportIssue>  $issues
     */
    private function hasErrors(array $issues): bool
    {
        foreach ($issues as $issue) {
            if ($issue->isError()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $flowRows
     * @param  list<array<string, mixed>>  $sectionRows
     * @param  list<array<string, mixed>>  $stepRows
     * @return list<ProcessFlowImportIssue>
     */
    private function detectOrphanStepSections(array $flowRows, array $sectionRows, array $stepRows): array
    {
        $issues = [];
        $sectionIndex = [];

        foreach ($sectionRows as $row) {
            $flowKey = ProcessFlowImportRowParser::string($row['flow_key'] ?? null);
            $sectionKey = ProcessFlowImportRowParser::string($row['section_key'] ?? null);
            if ($flowKey !== '' && $sectionKey !== '') {
                $sectionIndex[$flowKey][$sectionKey] = true;
            }
        }

        foreach ($stepRows as $rowIndex => $row) {
            $flowKey = ProcessFlowImportRowParser::string($row['flow_key'] ?? null);
            $sectionKey = ProcessFlowImportRowParser::string($row['section_key'] ?? null);
            if ($flowKey === '' || $sectionKey === '') {
                continue;
            }

            if (! isset($sectionIndex[$flowKey][$sectionKey])) {
                $line = $rowIndex + 2;
                $issues[] = new ProcessFlowImportIssue(
                    "Steps!A{$line}",
                    'section_key',
                    __('La section « :section » est introuvable pour le parcours « :flow » (feuille Steps, ligne :line).', [
                        'section' => $sectionKey,
                        'flow' => $flowKey,
                        'line' => $line,
                    ]),
                );
            }
        }

        return $issues;
    }

    /**
     * @param  list<array<string, mixed>>  $sectionRows
     * @param  list<array<string, mixed>>  $stepRows
     * @param  list<array<string, mixed>>  $documentRows
     * @param  list<ProcessFlowImportIssue>  $issues
     * @return list<ProcessFlowImportSectionData>
     */
    private function buildSectionsForFlow(
        string $flowKey,
        array $sectionRows,
        array $stepRows,
        array $documentRows,
        array &$issues,
    ): array {
        $sections = [];
        $globalOrder = 0;

        foreach ($sectionRows as $rowIndex => $row) {
            if (ProcessFlowImportRowParser::string($row['flow_key'] ?? null) !== $flowKey) {
                continue;
            }

            $line = $rowIndex + 2;
            $issues = array_merge($issues, $this->forbiddenFields->scanSectionRow($row, "Sections!A{$line}"));

            $titleFr = ProcessFlowImportRowParser::string($row['title_fr'] ?? null);
            $sectionKey = ProcessFlowImportRowParser::string($row['section_key'] ?? null);
            if ($sectionKey === '' && $titleFr !== '') {
                $sectionKey = Str::limit(Str::slug($titleFr), 64, '');
            }
            if ($sectionKey === '') {
                continue;
            }

            $steps = [];
            foreach ($stepRows as $stepRowIndex => $stepRow) {
                if (ProcessFlowImportRowParser::string($stepRow['flow_key'] ?? null) !== $flowKey) {
                    continue;
                }
                if (ProcessFlowImportRowParser::string($stepRow['section_key'] ?? null) !== $sectionKey) {
                    continue;
                }

                $globalOrder++;
                $stepOrder = ProcessFlowImportRowParser::int($stepRow['step_order'] ?? null, 0);
                $stepLine = $stepRowIndex + 2;
                $issues = array_merge($issues, $this->forbiddenFields->scanStepRow($stepRow, "Steps!A{$stepLine}"));

                $docCodes = $this->documentCodesForStep($flowKey, $globalOrder, $documentRows, $issues);

                $steps[] = new ProcessFlowImportStepData(
                    stepOrder: $stepOrder > 0 ? $stepOrder : $globalOrder,
                    globalStepOrder: $globalOrder,
                    stepType: strtoupper(ProcessFlowImportRowParser::string($stepRow['step_type'] ?? 'INFO')),
                    paymentType: $this->nullableUpper($stepRow['payment_type'] ?? null),
                    responsibleParty: $this->nullableUpper($stepRow['responsible_party'] ?? null),
                    title: [
                        'fr' => ProcessFlowImportRowParser::string($stepRow['title_fr'] ?? null),
                        'en' => ProcessFlowImportRowParser::string($stepRow['title_en'] ?? $stepRow['title_fr'] ?? null),
                    ],
                    amount: ProcessFlowImportRowParser::float($stepRow['amount'] ?? $stepRow['default_amount'] ?? 0),
                    isBlocking: ProcessFlowImportRowParser::bool($stepRow['is_blocking'] ?? true),
                    isRequired: ProcessFlowImportRowParser::bool($stepRow['is_required'] ?? true),
                    acceptedBanks: ProcessFlowImportRowParser::csvList($stepRow['accepted_banks'] ?? null),
                    documentTypeCodes: $docCodes,
                    estimatedDurationDays: ProcessFlowImportRowParser::nullableInt(
                        $stepRow['estimated_duration_days'] ?? $stepRow['duration_days'] ?? null,
                    ),
                    currency: strtoupper(ProcessFlowImportRowParser::string($stepRow['currency'] ?? 'XAF') ?: 'XAF'),
                );
            }

            $sections[] = new ProcessFlowImportSectionData(
                sectionKey: $sectionKey,
                title: [
                    'fr' => ProcessFlowImportRowParser::string($row['title_fr'] ?? null),
                    'en' => ProcessFlowImportRowParser::string($row['title_en'] ?? $row['title_fr'] ?? null),
                ],
                order: ProcessFlowImportRowParser::int($row['order'] ?? $row['section_order'] ?? null, count($sections) + 1),
                steps: $steps,
            );

            if ($steps === []) {
                $issues[] = new ProcessFlowImportIssue(
                    "Sections!A{$line}",
                    'section_key',
                    __('La section « :section » ne contient aucune étape.', ['section' => $sectionKey]),
                );
            }
        }

        usort($sections, static fn (ProcessFlowImportSectionData $a, ProcessFlowImportSectionData $b): int => $a->order <=> $b->order);

        return $sections;
    }

    /**
     * @param  list<array<string, mixed>>  $documentRows
     * @param  list<ProcessFlowImportIssue>  $issues
     * @return list<string>
     */
    private function documentCodesForStep(
        string $flowKey,
        int $globalStepOrder,
        array $documentRows,
        array &$issues,
    ): array {
        $codes = [];

        foreach ($documentRows as $rowIndex => $row) {
            if (ProcessFlowImportRowParser::string($row['flow_key'] ?? null) !== $flowKey) {
                continue;
            }

            $rowStepOrder = ProcessFlowImportRowParser::int($row['step_order'] ?? null, 0);
            if ($rowStepOrder !== $globalStepOrder) {
                continue;
            }

            $code = strtoupper(ProcessFlowImportRowParser::string($row['document_type_code'] ?? null));
            if ($code === '') {
                continue;
            }

            $codes[] = $code;
        }

        return array_values(array_unique($codes));
    }

    /**
     * @param  list<string>  $names
     */
    private function resolveSheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $names): ?Worksheet
    {
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $title = strtolower(str_replace(' ', '', $worksheet->getTitle()));
            foreach ($names as $name) {
                if ($title === strtolower(str_replace(' ', '', $name))) {
                    return $worksheet;
                }
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rowsFromSheet(Worksheet $sheet): array
    {
        $rows = $sheet->toArray(null, true, true, true);
        if ($rows === []) {
            return [];
        }

        $headerRow = array_shift($rows);
        $headers = [];
        foreach ($headerRow as $cell) {
            $key = strtolower(trim((string) $cell));
            if ($key !== '') {
                $headers[] = str_replace(' ', '_', $key);
            }
        }

        $parsed = [];
        foreach ($rows as $row) {
            $assoc = [];
            $values = array_values($row);
            $isEmpty = true;

            foreach ($headers as $index => $header) {
                $value = $values[$index] ?? null;
                if ($value !== null && trim((string) $value) !== '') {
                    $isEmpty = false;
                }
                $assoc[$header] = $value;
            }

            if (! $isEmpty) {
                $firstCell = trim((string) ($values[0] ?? ''));
                if ($firstCell !== '' && str_starts_with($firstCell, '#')) {
                    continue;
                }

                $parsed[] = $assoc;
            }
        }

        return $parsed;
    }

    private function nullableUpper(mixed $value): ?string
    {
        $string = ProcessFlowImportRowParser::string($value);

        return $string === '' ? null : strtoupper($string);
    }
}
