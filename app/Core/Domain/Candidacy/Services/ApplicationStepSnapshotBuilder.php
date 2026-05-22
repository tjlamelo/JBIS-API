<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Services;

use App\Core\Domain\Candidacy\States\ApplicationStepPaymentStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Support\Carbon;

final class ApplicationStepSnapshotBuilder
{
    /**
     * @return list<array<string, mixed>>
     */
    public function buildRows(int $applicationId, ProcessFlow $flow, Carbon $now): array
    {
        $flow->loadMissing(['steps.section']);

        $rows = [];
        foreach ($flow->steps as $step) {
            $rows[] = $this->mapStep($applicationId, $step, $now);
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapStep(int $applicationId, ProcessStep $step, Carbon $now): array
    {
        $stepType = $step->step_type;
        $stepTypeValue = $stepType instanceof ProcessStepType ? $stepType->value : (string) $stepType;

        $amountDue = (float) $step->default_amount;
        $paymentStatus = $amountDue > 0
            ? ApplicationStepPaymentStatus::Unpaid->value
            : ApplicationStepPaymentStatus::Waived->value;

        return [
            'application_id' => $applicationId,
            'process_step_id' => $step->id,
            'step_order' => (int) $step->step_order,
            'section_key' => $step->section?->key,
            'step_type' => $stepTypeValue,
            'payment_type' => $step->payment_type?->value ?? $step->payment_type,
            'responsible_party' => $step->responsible_party?->value ?? $step->responsible_party ?? 'CANDIDATE',
            'title' => json_encode($step->getTranslations('title'), JSON_THROW_ON_ERROR),
            'description' => $step->getTranslations('description') !== []
                ? json_encode($step->getTranslations('description'), JSON_THROW_ON_ERROR)
                : null,
            'is_blocking' => (bool) $step->is_blocking,
            'is_required' => (bool) $step->is_required,
            'requires_documents' => (bool) $step->requires_documents,
            'document_type_ids' => $step->document_type_ids !== null
                ? json_encode($step->document_type_ids, JSON_THROW_ON_ERROR)
                : null,
            'amount_due' => $amountDue,
            'amount_paid' => 0,
            'payment_status' => $paymentStatus,
            'status' => ApplicationStepStatus::Locked->value,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
