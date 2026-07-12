<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Candidacy\Services\CandidacyNotificationService;
use App\Core\Domain\Candidacy\States\ApplicationStepPaymentStatus;
use App\Core\Domain\Finance\Models\PaymentInstallment;
use Illuminate\Support\Facades\DB;

final class WaiveApplicationStepPaymentAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
        private readonly CandidacyNotificationService $candidacyNotifications,
    ) {}

    public function execute(ApplicationStep $step, ?int $staffUserId = null, ?string $reason = null): ApplicationStep
    {
        $result = DB::transaction(function () use ($step, $staffUserId, $reason): array {
            $step = ApplicationStep::query()->whereKey($step->id)->lockForUpdate()->firstOrFail();

            $step->update([
                'payment_status' => ApplicationStepPaymentStatus::Waived->value,
            ]);

            PaymentInstallment::query()
                ->where('application_step_id', $step->id)
                ->update(['status' => 'CANCELLED']);

            $this->activityLogger->log(
                (int) $step->application_id,
                'payment.waived',
                $step->id,
                $staffUserId,
                ['reason' => $reason],
            );

            $application = Application::query()
                ->with(['user:id,name,email'])
                ->find($step->application_id);

            return [$step->fresh(), $application];
        });

        /** @var ApplicationStep $step */
        /** @var Application|null $application */
        [$step, $application] = $result;

        if ($application !== null) {
            $this->candidacyNotifications->paymentWaived($application, $step);
        }

        return $step;
    }
}
