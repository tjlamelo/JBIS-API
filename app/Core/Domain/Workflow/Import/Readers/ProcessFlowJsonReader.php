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

final class ProcessFlowJsonReader
{
    public function __construct(
        private readonly ProcessFlowImportForbiddenFields $forbiddenFields,
    ) {}

    /**
     * @return array{payload: ProcessFlowImportPayload|null, issues: list<ProcessFlowImportIssue>}
     */
    public function read(string $filePath): array
    {
        $contents = file_get_contents($filePath);
        if ($contents === false) {
            return [
                'payload' => null,
                'issues' => [new ProcessFlowImportIssue('$', 'file', __('Impossible de lire le fichier JSON.'))],
            ];
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [
                'payload' => null,
                'issues' => [new ProcessFlowImportIssue('$', 'json', __('JSON invalide : :message', ['message' => $e->getMessage()]))],
            ];
        }

        $items = isset($decoded['flows']) && is_array($decoded['flows']) ? $decoded['flows'] : [$decoded];
        $flows = [];
        $issues = [];

        foreach ($items as $flowIndex => $item) {
            if (! is_array($item)) {
                $issues[] = new ProcessFlowImportIssue("flows[{$flowIndex}]", 'flow', __('Structure de parcours invalide.'));
                continue;
            }

            $flow = $this->parseFlow($item, "flows[{$flowIndex}]", $issues);
            if ($flow !== null) {
                $flows[] = $flow;
            }
        }

        if ($flows === []) {
            $issues[] = new ProcessFlowImportIssue('$', 'flows', __('Aucun parcours valide dans le fichier JSON.'));
        }

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
     * @param  array<string, mixed>  $item
     * @param  list<ProcessFlowImportIssue>  $issues
     */
    private function parseFlow(array $item, string $path, array &$issues): ?ProcessFlowImportFlowData
    {
        $flowKey = ProcessFlowImportRowParser::string($item['flow_key'] ?? null);
        if ($flowKey === '') {
            $issues[] = new ProcessFlowImportIssue($path, 'flow_key', __('La clé flow_key est obligatoire.'));

            return null;
        }

        $issues = array_merge($issues, $this->forbiddenFields->scanJsonFlow($item, $path));

        $name = $this->parseTranslations($item['name'] ?? [], "{$path}.name");
        $sections = $this->parseSections($item['sections'] ?? [], $path, $issues);

        return new ProcessFlowImportFlowData(
            flowKey: $flowKey,
            countryCode: ProcessFlowImportRowParser::string($item['country_code'] ?? null),
            name: $name,
            fileOpeningFee: ProcessFlowImportRowParser::nullableFloat($item['file_opening_fee'] ?? null),
            totalProcedureFees: ProcessFlowImportRowParser::nullableFloat($item['total_procedure_fees'] ?? null),
            estimatedDurationDays: ProcessFlowImportRowParser::nullableInt($item['estimated_duration_days'] ?? null),
            sections: $sections,
        );
    }

    /**
     * @param  mixed  $raw
     * @return array{fr: string, en: string}
     */
    private function parseTranslations(mixed $raw, string $path): array
    {
        if (is_string($raw)) {
            return ['fr' => trim($raw), 'en' => ''];
        }

        if (! is_array($raw)) {
            return ['fr' => '', 'en' => ''];
        }

        return [
            'fr' => ProcessFlowImportRowParser::string($raw['fr'] ?? null),
            'en' => ProcessFlowImportRowParser::string($raw['en'] ?? null),
        ];
    }

    /**
     * @param  mixed  $raw
     * @param  list<ProcessFlowImportIssue>  $issues
     * @return list<ProcessFlowImportSectionData>
     */
    private function parseSections(mixed $raw, string $flowPath, array &$issues): array
    {
        if (! is_array($raw)) {
            $issues[] = new ProcessFlowImportIssue("{$flowPath}.sections", 'sections', __('Les sections doivent être un tableau.'));

            return [];
        }

        $sections = [];
        $globalOrder = 0;

        foreach ($raw as $sectionIndex => $sectionRow) {
            if (! is_array($sectionRow)) {
                continue;
            }

            $sectionPath = "{$flowPath}.sections[{$sectionIndex}]";
            $issues = array_merge($issues, $this->forbiddenFields->scanSectionRow($sectionRow, $sectionPath));
            $sectionKey = ProcessFlowImportRowParser::string($sectionRow['section_key'] ?? $sectionRow['key'] ?? null);

            $steps = [];
            $stepRows = is_array($sectionRow['steps'] ?? null) ? $sectionRow['steps'] : [];
            foreach ($stepRows as $stepIndex => $stepRow) {
                if (! is_array($stepRow)) {
                    continue;
                }

                $globalOrder++;
                $stepPath = "{$sectionPath}.steps[{$stepIndex}]";
                $stepOrder = ProcessFlowImportRowParser::int($stepRow['step_order'] ?? null, $globalOrder);
                $docCodes = [];

                $issues = array_merge($issues, $this->forbiddenFields->scanStepRow($stepRow, $stepPath));

                if (is_array($stepRow['required_documents'] ?? null)) {
                    foreach ($stepRow['required_documents'] as $docIndex => $code) {
                        $docCodes[] = strtoupper(ProcessFlowImportRowParser::string($code));
                    }
                }

                $steps[] = new ProcessFlowImportStepData(
                    stepOrder: $stepOrder,
                    globalStepOrder: $globalOrder,
                    stepType: strtoupper(ProcessFlowImportRowParser::string($stepRow['step_type'] ?? 'INFO')),
                    paymentType: $this->nullableUpper($stepRow['payment_type'] ?? null),
                    responsibleParty: $this->nullableUpper($stepRow['responsible_party'] ?? null),
                    title: $this->parseTranslations($stepRow['title'] ?? [], "{$stepPath}.title"),
                    amount: ProcessFlowImportRowParser::float($stepRow['amount'] ?? $stepRow['default_amount'] ?? 0),
                    isBlocking: ProcessFlowImportRowParser::bool($stepRow['is_blocking'] ?? true),
                    isRequired: ProcessFlowImportRowParser::bool($stepRow['is_required'] ?? true),
                    acceptedBanks: ProcessFlowImportRowParser::csvList($stepRow['accepted_banks'] ?? null),
                    documentTypeCodes: array_values(array_unique(array_filter($docCodes))),
                    estimatedDurationDays: ProcessFlowImportRowParser::nullableInt(
                        $stepRow['estimated_duration_days'] ?? $stepRow['duration_days'] ?? null,
                    ),
                    currency: strtoupper(ProcessFlowImportRowParser::string($stepRow['currency'] ?? 'XAF') ?: 'XAF'),
                );
            }

            $sections[] = new ProcessFlowImportSectionData(
                sectionKey: $sectionKey,
                title: $this->parseTranslations($sectionRow['title'] ?? [], "{$sectionPath}.title"),
                order: ProcessFlowImportRowParser::int($sectionRow['order'] ?? $sectionRow['section_order'] ?? null, count($sections) + 1),
                steps: $steps,
            );
        }

        usort($sections, static fn (ProcessFlowImportSectionData $a, ProcessFlowImportSectionData $b): int => $a->order <=> $b->order);

        return $sections;
    }

    private function nullableUpper(mixed $value): ?string
    {
        $string = ProcessFlowImportRowParser::string($value);

        return $string === '' ? null : strtoupper($string);
    }
}
