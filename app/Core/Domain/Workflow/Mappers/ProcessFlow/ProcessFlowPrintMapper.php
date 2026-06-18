<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Mappers\ProcessFlow;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowPrintViewModel;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessFlowSection;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Support\Carbon;

final class ProcessFlowPrintMapper
{
    public function map(ProcessFlow $flow, string $locale = 'fr'): ProcessFlowPrintViewModel
    {
        $flow->loadMissing(['program', 'offer.trade', 'country', 'sections.steps']);

        $locale = $this->normalizeLocale($locale);
        $documentLabels = $this->resolveDocumentTypeLabels($flow, $locale);

        $sections = $flow->sections
            ->sortBy('section_order')
            ->values()
            ->map(fn (ProcessFlowSection $section, int $index) => $this->mapSection(
                $section,
                $index + 1,
                $locale,
                $documentLabels,
            ))
            ->all();

        $stepsCount = (int) $flow->sections->sum(fn (ProcessFlowSection $s) => $s->steps->count());

        $status = $flow->status;
        $statusValue = is_object($status) && property_exists($status, 'value')
            ? $status->value
            : (string) $status;

        $opening = (float) $flow->file_opening_fee;
        $procedure = (float) $flow->total_procedure_fees;

        return new ProcessFlowPrintViewModel(
            locale: $locale,
            title: $this->trans($flow, 'name', $locale),
            statusLabel: $this->statusLabel($statusValue, $locale),
            version: (int) $flow->version,
            generatedAt: Carbon::now()->locale($locale)->isoFormat('LLL'),
            programLabel: $flow->program ? $this->trans($flow->program, 'name', $locale) : null,
            offerLabel: $flow->offer ? $this->offerLabel($flow->offer, $locale) : null,
            countryLabel: $flow->country ? $this->trans($flow->country, 'name', $locale) : null,
            stepsCount: $stepsCount,
            fileOpeningFeeLabel: $opening > 0
                ? number_format($opening, 0, ',', ' ').' XAF'
                : null,
            totalProcedureFeesLabel: $procedure > 0
                ? number_format($procedure, 0, ',', ' ').' XAF'
                : null,
            sections: $sections,
        );
    }

    /**
     * @param  array<int, string>  $documentLabels
     * @return array<string, mixed>
     */
    private function mapSection(
        ProcessFlowSection $section,
        int $order,
        string $locale,
        array $documentLabels,
    ): array {
        return [
            'order' => $order,
            'key' => $section->key,
            'title' => $this->trans($section, 'title', $locale),
            'description' => $this->transNullable($section, 'description', $locale),
            'color' => $section->color ?: '#01233c',
            'icon' => $section->icon,
            'visible_after' => $section->visible_after_section_key,
            'steps' => $section->steps
                ->sortBy('step_order')
                ->values()
                ->map(fn (ProcessStep $step, int $i) => $this->mapStep($step, $i + 1, $locale, $documentLabels))
                ->all(),
        ];
    }

    /**
     * @param  array<int, string>  $documentLabels
     * @return array<string, mixed>
     */
    private function mapStep(
        ProcessStep $step,
        int $order,
        string $locale,
        array $documentLabels,
    ): array {
        $amount = (float) $step->default_amount;
        $ids = is_array($step->document_type_ids) ? $step->document_type_ids : [];
        $labels = [];
        foreach ($ids as $id) {
            $intId = (int) $id;
            if ($intId > 0 && isset($documentLabels[$intId])) {
                $labels[] = $documentLabels[$intId];
            }
        }

        return [
            'order' => $step->step_order > 0 ? (int) $step->step_order : $order,
            'title' => $this->trans($step, 'title', $locale),
            'description' => $this->transNullable($step, 'description', $locale),
            'step_type_label' => $this->stepTypeLabel($step, $locale),
            'amount' => $amount > 0
                ? number_format($amount, 0, ',', ' ').' XAF'
                : null,
            'requires_documents' => (bool) $step->requires_documents,
            'document_labels' => $labels,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveDocumentTypeLabels(ProcessFlow $flow, string $locale): array
    {
        $ids = [];
        foreach ($flow->sections as $section) {
            foreach ($section->steps as $step) {
                if (! is_array($step->document_type_ids)) {
                    continue;
                }
                foreach ($step->document_type_ids as $id) {
                    $intId = (int) $id;
                    if ($intId > 0) {
                        $ids[$intId] = $intId;
                    }
                }
            }
        }

        if ($ids === []) {
            return [];
        }

        return DocumentType::query()
            ->whereIn('id', array_values($ids))
            ->get()
            ->mapWithKeys(fn (DocumentType $type) => [
                $type->id => $this->documentTypeLabel($type, $locale),
            ])
            ->all();
    }

    private function documentTypeLabel(DocumentType $type, string $locale): string
    {
        if (method_exists($type, 'getTranslation')) {
            $primary = (string) $type->getTranslation('label', $locale, false);
            if ($primary !== '') {
                return $primary;
            }

            $fallback = $locale === 'fr' ? 'en' : 'fr';

            return (string) $type->getTranslation('label', $fallback, false) ?: $type->code;
        }

        return (string) $type->code;
    }

    private function offerLabel(\App\Core\Domain\Catalog\Models\Offer $offer, string $locale): string
    {
        $translations = $offer->resolvedTitleTranslations();
        $value = trim((string) ($translations[$locale] ?? $translations[$locale === 'fr' ? 'en' : 'fr'] ?? ''));

        return $value;
    }

    private function trans(object $model, string $field, string $locale): string
    {
        if (method_exists($model, 'getTranslation')) {
            return (string) $model->getTranslation($field, $locale, false)
                ?: (string) $model->getTranslation($field, $locale === 'fr' ? 'en' : 'fr', false);
        }

        return '';
    }

    private function transNullable(object $model, string $field, string $locale): ?string
    {
        $value = trim($this->trans($model, $field, $locale));

        return $value !== '' ? $value : null;
    }

    private function normalizeLocale(string $locale): string
    {
        return str_starts_with(strtolower($locale), 'en') ? 'en' : 'fr';
    }

    private function statusLabel(string $status, string $locale): string
    {
        return match ($status) {
            ProcessFlowStatus::Published->value => $locale === 'fr' ? 'Publié' : 'Published',
            ProcessFlowStatus::Archived->value => $locale === 'fr' ? 'Archivé' : 'Archived',
            default => $locale === 'fr' ? 'Brouillon' : 'Draft',
        };
    }

    private function stepTypeLabel(ProcessStep $step, string $locale): string
    {
        $raw = $step->step_type;
        $value = is_object($raw) && property_exists($raw, 'value') ? $raw->value : (string) $raw;

        $fr = match ($value) {
            ProcessStepType::DocumentCollection->value => 'Collecte documents',
            ProcessStepType::Payment->value => 'Paiement',
            ProcessStepType::Service->value => 'Service JBIS',
            ProcessStepType::Interview->value => 'Entretien',
            ProcessStepType::Signing->value => 'Signature',
            ProcessStepType::Administrative->value => 'Administratif',
            ProcessStepType::ImmigrationExit->value => 'Sortie immigration',
            default => 'Information',
        };

        if ($locale === 'fr') {
            return $fr;
        }

        return match ($value) {
            ProcessStepType::DocumentCollection->value => 'Document collection',
            ProcessStepType::Payment->value => 'Payment',
            ProcessStepType::Service->value => 'JBIS service',
            ProcessStepType::Interview->value => 'Interview',
            ProcessStepType::Signing->value => 'Signing',
            ProcessStepType::Administrative->value => 'Administrative',
            ProcessStepType::ImmigrationExit->value => 'Immigration exit',
            default => 'Information',
        };
    }
}
