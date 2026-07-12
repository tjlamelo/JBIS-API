<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Candidacy\Services\CandidacyNotificationService;
use App\Core\Domain\Finance\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ConfirmApplicationStepPaymentAction
{
    public function __construct(
        private readonly RecordApplicationStepPaymentAction $recordPaymentAction,
        private readonly ApplicationActivityLogger $activityLogger,
        private readonly CandidacyNotificationService $candidacyNotifications,
    ) {}

    public function execute(Payment $payment, int $staffUserId): Payment
    {
        if ($payment->status !== 'PENDING') {
            throw new InvalidArgumentException(__('Ce paiement ne peut pas être confirmé.'));
        }

        $result = DB::transaction(function () use ($payment, $staffUserId): array {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $step = ApplicationStep::query()->whereKey($payment->application_step_id)->lockForUpdate()->firstOrFail();
            $application = Application::query()->whereKey($payment->application_id)->lockForUpdate()->firstOrFail();

            $payment->update([
                'status' => 'COMPLETED',
                'payment_date' => now(),
            ]);

            $this->recordPaymentAction->syncAfterPaymentChange($step);

            $this->activityLogger->log(
                (int) $payment->application_id,
                ApplicationActivityLogger::ACTION_PAYMENT_RECORDED,
                $step->id,
                $staffUserId,
                [
                    'amount' => (float) $payment->amount,
                    'payment_id' => $payment->id,
                    'confirmed' => true,
                ],
            );

            return [
                $payment->fresh(),
                $application->fresh(['user:id,name,email']),
                $step->fresh(),
            ];
        });

        /** @var Payment $payment */
        /** @var Application $application */
        /** @var ApplicationStep $step */
        [$payment, $application, $step] = $result;

        $this->candidacyNotifications->paymentConfirmed($application, $step, (float) $payment->amount);

        return $payment;
    }
}
