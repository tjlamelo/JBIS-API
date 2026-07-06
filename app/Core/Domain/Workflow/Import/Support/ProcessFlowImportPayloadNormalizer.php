<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\Support;

use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportFlowData;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportPayload;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportSectionData;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportStepData;
use Illuminate\Support\Str;

final class ProcessFlowImportPayloadNormalizer
{
    public function normalize(ProcessFlowImportPayload $payload): ProcessFlowImportPayload
    {
        return new ProcessFlowImportPayload(
            array_map(fn (ProcessFlowImportFlowData $flow): ProcessFlowImportFlowData => $this->normalizeFlow($flow), $payload->flows),
        );
    }

    private function normalizeFlow(ProcessFlowImportFlowData $flow): ProcessFlowImportFlowData
    {
        $sections = [];
        foreach ($flow->sections as $index => $section) {
            $sections[] = $this->normalizeSection($section, $index);
        }

        return new ProcessFlowImportFlowData(
            flowKey: $flow->flowKey,
            countryCode: $flow->countryCode,
            name: $flow->name,
            fileOpeningFee: $flow->fileOpeningFee,
            totalProcedureFees: $flow->totalProcedureFees,
            estimatedDurationDays: $flow->estimatedDurationDays,
            sections: $sections,
        );
    }

    private function normalizeSection(ProcessFlowImportSectionData $section, int $index): ProcessFlowImportSectionData
    {
        $sectionKey = $section->sectionKey;
        if ($sectionKey === '') {
            $source = $section->title['fr'] !== ''
                ? $section->title['fr']
                : ($section->title['en'] !== '' ? $section->title['en'] : 'section-'.($index + 1));
            $sectionKey = Str::slug($source);
            if ($sectionKey === '') {
                $sectionKey = 'section-'.($index + 1);
            }
            $sectionKey = Str::limit($sectionKey, 64, '');
        }

        $steps = [];
        foreach ($section->steps as $step) {
            $steps[] = new ProcessFlowImportStepData(
                stepOrder: $step->stepOrder,
                globalStepOrder: $step->globalStepOrder,
                stepType: $step->stepType,
                paymentType: $step->paymentType,
                responsibleParty: $step->responsibleParty,
                title: $step->title,
                amount: $step->amount,
                isBlocking: $step->isBlocking,
                isRequired: $step->isRequired,
                acceptedBanks: $step->acceptedBanks,
                documentTypeCodes: $step->documentTypeCodes,
                estimatedDurationDays: $step->estimatedDurationDays,
                currency: $step->currency !== '' ? $step->currency : 'XAF',
            );
        }

        return new ProcessFlowImportSectionData(
            sectionKey: $sectionKey,
            title: $section->title,
            order: $section->order,
            steps: $steps,
        );
    }
}
