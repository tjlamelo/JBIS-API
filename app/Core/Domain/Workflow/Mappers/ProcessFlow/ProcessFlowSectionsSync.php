<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Mappers\ProcessFlow;

use App\Core\Domain\Workflow\Mappers\Shared\TranslatablePayloadNormalizer;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessFlowSection;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\PaymentType;
use App\Core\Domain\Workflow\States\ProcessStepType;
use App\Core\Domain\Workflow\States\ResponsibleParty;

final class ProcessFlowSectionsSync
{
    /**
     * @param  list<array<string, mixed>>  $sections
     */
    public function sync(ProcessFlow $flow, array $sections): void
    {
        $flow->steps()->delete();
        $flow->sections()->delete();

        if ($sections === []) {
            return;
        }

        $globalStepOrder = 0;

        foreach ($sections as $index => $sectionRow) {
            $title = TranslatablePayloadNormalizer::normalize($sectionRow['title'] ?? '');
            if ($title['fr'] === '' && $title['en'] === '') {
                continue;
            }

            $section = new ProcessFlowSection([
                'process_flow_id' => $flow->id,
                'key' => (string) ($sectionRow['key'] ?? 'section_'.($index + 1)),
                'section_order' => isset($sectionRow['section_order']) && (int) $sectionRow['section_order'] > 0
                    ? (int) $sectionRow['section_order']
                    : $index + 1,
                'color' => $sectionRow['color'] ?? null,
                'icon' => $sectionRow['icon'] ?? null,
                'visible_after_section_key' => isset($sectionRow['visible_after_section_key']) && $sectionRow['visible_after_section_key'] !== ''
                    ? (string) $sectionRow['visible_after_section_key']
                    : null,
            ]);
            $section->setTranslations('title', $title);

            $description = TranslatablePayloadNormalizer::normalizeNullable($sectionRow['description'] ?? null);
            if ($description !== null) {
                $section->setTranslations('description', $description);
            }

            $section->save();

            $steps = is_array($sectionRow['steps'] ?? null) ? $sectionRow['steps'] : [];
            foreach ($steps as $stepRow) {
                $stepTitle = TranslatablePayloadNormalizer::normalize($stepRow['title'] ?? '');
                if ($stepTitle['fr'] === '' && $stepTitle['en'] === '') {
                    continue;
                }

                $globalStepOrder++;
                $stepType = ProcessStepType::tryFrom((string) ($stepRow['step_type'] ?? ''))
                    ?? ProcessStepType::Info;

                $documentTypeIds = $this->normalizeDocumentTypeIds($stepRow['document_type_ids'] ?? null);
                $requiresDocuments = (bool) ($stepRow['requires_documents'] ?? false);

                $step = new ProcessStep([
                    'process_flow_id' => $flow->id,
                    'process_flow_section_id' => $section->id,
                    'step_type' => $stepType->value,
                    'payment_type' => isset($stepRow['payment_type'])
                        ? (PaymentType::tryFrom((string) $stepRow['payment_type'])?->value)
                        : null,
                    'responsible_party' => ResponsibleParty::tryFrom((string) ($stepRow['responsible_party'] ?? ''))
                        ?->value ?? ResponsibleParty::Candidate->value,
                    'step_order' => isset($stepRow['step_order']) && (int) $stepRow['step_order'] > 0
                        ? (int) $stepRow['step_order']
                        : $globalStepOrder,
                    'is_blocking' => (bool) ($stepRow['is_blocking'] ?? true),
                    'is_required' => (bool) ($stepRow['is_required'] ?? true),
                    'default_amount' => $stepRow['default_amount'] ?? 0,
                    'requires_documents' => $requiresDocuments,
                    'document_type_ids' => $documentTypeIds !== [] ? $documentTypeIds : null,
                    'accepted_banks' => is_array($stepRow['accepted_banks'] ?? null) ? $stepRow['accepted_banks'] : null,
                    'internal_note' => $stepRow['internal_note'] ?? null,
                    'estimated_duration_days' => isset($stepRow['estimated_duration_days'])
                        ? (int) $stepRow['estimated_duration_days']
                        : null,
                    'sla_alert_days' => isset($stepRow['sla_alert_days'])
                        ? (int) $stepRow['sla_alert_days']
                        : null,
                ]);

                $step->setTranslations('title', $stepTitle);

                $stepDescription = TranslatablePayloadNormalizer::normalizeNullable($stepRow['description'] ?? null);
                if ($stepDescription !== null) {
                    $step->setTranslations('description', $stepDescription);
                }

                $step->save();
            }
        }
    }

    /**
     * @return list<int>
     */
    private function normalizeDocumentTypeIds(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $id): int => (int) $id,
            $raw,
        ), static fn (int $id): bool => $id > 0)));
    }
}
