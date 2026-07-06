<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import;

use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportFlowData;
use App\Core\Domain\Workflow\States\PaymentType;
use App\Core\Domain\Workflow\States\ProcessStepType;

final class ProcessFlowImportDerivedTotals
{
    public function fileOpeningFee(ProcessFlowImportFlowData $flow): float
    {
        $sum = 0.0;

        foreach ($flow->sections as $section) {
            foreach ($section->steps as $step) {
                if ($step->stepType !== ProcessStepType::Payment->value) {
                    continue;
                }

                if (($step->paymentType ?? '') === PaymentType::FileOpening->value) {
                    $sum += $step->amount;
                }
            }
        }

        return round($sum, 2);
    }

    public function procedureFees(ProcessFlowImportFlowData $flow): float
    {
        $sum = 0.0;

        foreach ($flow->sections as $section) {
            foreach ($section->steps as $step) {
                if ($step->stepType !== ProcessStepType::Payment->value) {
                    continue;
                }

                if (($step->paymentType ?? '') !== PaymentType::FileOpening->value) {
                    $sum += $step->amount;
                }
            }
        }

        return round($sum, 2);
    }

    public function estimatedDurationDays(ProcessFlowImportFlowData $flow): ?int
    {
        $sum = 0;
        $hasAny = false;

        foreach ($flow->sections as $section) {
            foreach ($section->steps as $step) {
                if ($step->estimatedDurationDays === null) {
                    continue;
                }

                $hasAny = true;
                $sum += $step->estimatedDurationDays;
            }
        }

        return $hasAny ? $sum : null;
    }

    public function amountsMatch(?float $provided, float $calculated): bool
    {
        if ($provided === null) {
            return true;
        }

        return abs($provided - $calculated) < 0.01;
    }
}
