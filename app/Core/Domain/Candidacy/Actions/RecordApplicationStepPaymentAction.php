<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Candidacy\States\ApplicationStepPaymentStatus;
use App\Core\Domain\Finance\Models\Payment;
use App\Core\Domain\Finance\Models\PaymentInstallment;
use App\Core\Domain\Finance\Models\PaymentSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class RecordApplicationStepPaymentAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
    ) {}

    /**
     * @param  'FULL'|'PARTIAL'|'REFUND'  $paymentType
     * @param  'PENDING'|'COMPLETED'|'FAILED'|'REFUNDED'  $status
     */
    public function execute(
        ApplicationStep $step,
        float $amount,
        string $paymentType = 'FULL',
        string $status = 'COMPLETED',
        ?string $reference = null,
        ?int $recordedByUserId = null,
        string $paymentMethod = 'BANK_TRANSFER',
        ?string $description = null,
    ): Payment {
        return DB::transaction(function () use ($step, $amount, $paymentType, $status, $reference, $recordedByUserId, $paymentMethod, $description): Payment {
            $step = ApplicationStep::query()->whereKey($step->id)->lockForUpdate()->firstOrFail();
            $application = Application::query()->whereKey($step->application_id)->lockForUpdate()->firstOrFail();

            $signedAmount = $paymentType === 'REFUND' ? -abs($amount) : abs($amount);

            $payment = Payment::query()->create([
                'application_id' => $application->id,
                'application_step_id' => $step->id,
                'user_id' => $recordedByUserId ?? $application->user_id,
                'amount' => $signedAmount,
                'currency' => 'XAF',
                'payment_type' => $paymentType,
                'payment_method' => $paymentMethod,
                'payment_date' => Carbon::now(),
                'status' => $status,
                'reference' => $reference,
                'description' => $description,
            ]);

            if ($status === 'COMPLETED') {
                $this->syncAfterPaymentChange($step);
            }

            if ($status === 'COMPLETED') {
                $this->activityLogger->log(
                    $application->id,
                    ApplicationActivityLogger::ACTION_PAYMENT_RECORDED,
                    $step->id,
                    $recordedByUserId,
                    [
                        'amount' => $signedAmount,
                        'payment_type' => $paymentType,
                        'payment_id' => $payment->id,
                    ],
                );
            } elseif ($status === 'PENDING') {
                $this->activityLogger->log(
                    $application->id,
                    'payment.declared',
                    $step->id,
                    $recordedByUserId,
                    [
                        'amount' => $signedAmount,
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                    ],
                );
            }

            return $payment;
        });
    }

    public function syncAfterPaymentChange(ApplicationStep $step): void
    {
        $step = $step->fresh() ?? $step;
        $this->syncStepPaymentTotals($step);
        $application = Application::query()->find($step->application_id);
        if ($application !== null) {
            $this->syncApplicationTotals($application);
        }
        $this->syncInstallment($step->fresh() ?? $step);
    }

    private function syncStepPaymentTotals(ApplicationStep $step): void
    {
        $paid = (float) Payment::query()
            ->where('application_step_id', $step->id)
            ->where('status', 'COMPLETED')
            ->sum('amount');

        $due = (float) $step->amount_due;
        $paymentStatus = match (true) {
            $due <= 0 => ApplicationStepPaymentStatus::Waived,
            $paid <= 0 => ApplicationStepPaymentStatus::Unpaid,
            $paid < $due => ApplicationStepPaymentStatus::Partial,
            $paid === $due => ApplicationStepPaymentStatus::Paid,
            default => ApplicationStepPaymentStatus::Overpaid,
        };

        $step->update([
            'amount_paid' => max(0, $paid),
            'payment_status' => $paymentStatus,
        ]);
    }

    private function syncApplicationTotals(Application $application): void
    {
        $paid = (float) Payment::query()
            ->where('application_id', $application->id)
            ->where('status', 'COMPLETED')
            ->sum('amount');

        $application->update(['total_paid' => max(0, $paid)]);

        PaymentSchedule::query()
            ->where('application_id', $application->id)
            ->update(['paid_amount' => max(0, $paid)]);
    }

    private function syncInstallment(ApplicationStep $step): void
    {
        $installment = PaymentInstallment::query()
            ->where('application_step_id', $step->id)
            ->first();

        if ($installment === null) {
            return;
        }

        $paid = (float) $step->amount_paid;
        $due = (float) $step->amount_due;

        $installment->update([
            'paid_at' => $paid >= $due && $due > 0 ? Carbon::now() : null,
            'status' => match (true) {
                $due <= 0 => 'CANCELLED',
                $paid >= $due => 'PAID',
                default => 'PENDING',
            },
        ]);
    }
}
