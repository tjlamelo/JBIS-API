<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Services\ProcessFlow;

use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\PaymentType;
use App\Core\Domain\Workflow\States\ProcessStepType;

/**
 * Recalcule les totaux du parcours à partir des étapes PAYMENT.
 * Les frais d'ouverture (FILE_OPENING) ne sont pas inclus dans total_procedure_fees.
 */
final class ProcessFlowFeeRecalculator
{
    public function recalculate(ProcessFlow $flow): ProcessFlow
    {
        $flow->loadMissing('steps');

        $opening = 0.0;
        $procedure = 0.0;

        foreach ($flow->steps as $step) {
            if (! $this->isPaymentStep($step)) {
                continue;
            }

            $amount = (float) $step->default_amount;
            if ($amount <= 0) {
                continue;
            }

            if ($this->isFileOpening($step)) {
                $opening += $amount;
            } else {
                $procedure += $amount;
            }
        }

        $flow->file_opening_fee = number_format($opening, 2, '.', '');
        $flow->total_procedure_fees = number_format($procedure, 2, '.', '');
        $flow->save();

        return $flow->refresh();
    }

    private function isPaymentStep(ProcessStep $step): bool
    {
        $type = $step->step_type;
        $value = is_object($type) && property_exists($type, 'value')
            ? $type->value
            : (string) $type;

        return $value === ProcessStepType::Payment->value;
    }

    private function isFileOpening(ProcessStep $step): bool
    {
        $paymentType = $step->payment_type;
        $value = is_object($paymentType) && property_exists($paymentType, 'value')
            ? $paymentType->value
            : (string) ($paymentType ?? '');

        return $value === PaymentType::FileOpening->value;
    }
}
