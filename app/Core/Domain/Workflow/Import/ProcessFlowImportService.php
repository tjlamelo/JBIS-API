<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import;

use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportContext;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportFlowData;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportFlowSummary;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportIssue;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportPayload;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportResult;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessFlowSection;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\Services\ProcessFlow\ProcessFlowFeeRecalculator;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use App\Core\Domain\Workflow\States\ProcessStepType;
use App\Core\Domain\Workflow\States\ResponsibleParty;
use Illuminate\Support\Facades\DB;

final class ProcessFlowImportService
{
    public function __construct(
        private readonly ProcessFlowImportValidator $validator,
        private readonly ProcessFlowFeeRecalculator $feeRecalculator,
        private readonly ProcessFlowImportDerivedTotals $derivedTotals,
    ) {}

    /**
     * @param  list<ProcessFlowImportIssue>  $warnings
     */
    public function import(
        ProcessFlowImportPayload $payload,
        bool $commit = false,
        ProcessFlowImportContext $context = new ProcessFlowImportContext(),
        array $warnings = [],
    ): ProcessFlowImportResult {
        $issues = $this->validator->validate($payload);

        if ($issues !== []) {
            return new ProcessFlowImportResult(
                committed: $commit,
                success: false,
                issues: $issues,
                warnings: $warnings,
                summaries: [],
                createdFlowIds: [],
            );
        }

        $summaries = array_map(
            fn (ProcessFlowImportFlowData $flow): ProcessFlowImportFlowSummary => $this->buildSummary($flow),
            $payload->flows,
        );

        if (! $commit) {
            return new ProcessFlowImportResult(
                committed: false,
                success: true,
                issues: [],
                warnings: $warnings,
                summaries: $summaries,
                createdFlowIds: [],
            );
        }

        $createdFlowIds = DB::transaction(function () use ($payload, $context): array {
            $ids = [];
            foreach ($payload->flows as $flow) {
                $ids[] = $this->persistFlow($flow, $context)->id;
            }

            return $ids;
        });

        return new ProcessFlowImportResult(
            committed: true,
            success: true,
            issues: [],
            warnings: $warnings,
            summaries: $summaries,
            createdFlowIds: $createdFlowIds,
        );
    }

    private function buildSummary(ProcessFlowImportFlowData $flow): ProcessFlowImportFlowSummary
    {
        $versioning = $this->validator->resolveVersioning($flow->flowKey);
        $stepsCount = 0;
        $documentsCount = 0;

        foreach ($flow->sections as $section) {
            $stepsCount += count($section->steps);
            foreach ($section->steps as $step) {
                $documentsCount += count($step->documentTypeCodes);
            }
        }

        return new ProcessFlowImportFlowSummary(
            flowKey: $flow->flowKey,
            flowGroupId: $versioning['flow_group_id'],
            version: $versioning['version'],
            isNewGroup: $versioning['is_new_group'],
            sectionsCount: count($flow->sections),
            stepsCount: $stepsCount,
            documentsCount: $documentsCount,
            status: ProcessFlowStatus::Draft->value,
        );
    }

    private function persistFlow(ProcessFlowImportFlowData $flow, ProcessFlowImportContext $context): ProcessFlow
    {
        $versioning = $this->validator->resolveVersioning($flow->flowKey);
        $country = Country::query()->where('code', strtoupper($flow->countryCode))->firstOrFail();
        $documentMap = $this->validator->documentTypeCodeMap();

        $fileOpeningFee = $flow->fileOpeningFee ?? $this->derivedTotals->fileOpeningFee($flow);
        $procedureFees = $flow->totalProcedureFees ?? $this->derivedTotals->procedureFees($flow);
        $duration = $flow->estimatedDurationDays ?? $this->derivedTotals->estimatedDurationDays($flow);

        $processFlow = new ProcessFlow([
            'flow_group_id' => $versioning['flow_group_id'],
            'import_key' => $flow->flowKey,
            'imported_by' => $context->importedByUserId,
            'version' => $versioning['version'],
            'status' => ProcessFlowStatus::Draft->value,
            'country_id' => $country->id,
            'estimated_duration_days' => $duration,
            'file_opening_fee' => $fileOpeningFee,
            'total_procedure_fees' => $procedureFees,
        ]);
        $processFlow->setTranslations('name', $flow->name);
        $processFlow->save();

        foreach ($flow->sections as $sectionData) {
            $section = new ProcessFlowSection([
                'process_flow_id' => $processFlow->id,
                'key' => $sectionData->sectionKey,
                'section_order' => $sectionData->order,
            ]);
            $section->setTranslations('title', $sectionData->title);
            $section->save();

            foreach ($sectionData->steps as $stepData) {
                $documentTypeIds = $this->resolveDocumentTypeIds($stepData->documentTypeCodes, $documentMap);
                $requiresDocuments = $documentTypeIds !== []
                    || $stepData->stepType === ProcessStepType::DocumentCollection->value;

                $step = new ProcessStep([
                    'process_flow_id' => $processFlow->id,
                    'process_flow_section_id' => $section->id,
                    'step_type' => $stepData->stepType,
                    'payment_type' => $stepData->paymentType ?: null,
                    'responsible_party' => $stepData->responsibleParty ?: ResponsibleParty::Candidate->value,
                    'step_order' => $stepData->globalStepOrder,
                    'is_blocking' => $stepData->isBlocking,
                    'is_required' => $stepData->isRequired,
                    'default_amount' => $stepData->amount,
                    'accepted_banks' => $stepData->acceptedBanks !== [] ? $stepData->acceptedBanks : null,
                    'requires_documents' => $requiresDocuments,
                    'document_type_ids' => null,
                    'estimated_duration_days' => $stepData->estimatedDurationDays,
                ]);

                $step->setTranslations('title', $stepData->title);
                $step->save();

                if ($documentTypeIds !== []) {
                    $step->documentTypes()->sync($documentTypeIds);
                }
            }
        }

        $this->feeRecalculator->recalculate($processFlow);

        return $processFlow->fresh(['sections.steps', 'steps']);
    }

    /**
     * @param  list<string>  $codes
     * @param  array<string, int>  $documentMap
     * @return list<int>
     */
    private function resolveDocumentTypeIds(array $codes, array $documentMap): array
    {
        $ids = [];
        foreach ($codes as $code) {
            $normalized = strtoupper(trim($code));
            if ($normalized === '' || ! isset($documentMap[$normalized])) {
                continue;
            }
            $ids[] = $documentMap[$normalized];
        }

        return array_values(array_unique($ids));
    }
}
