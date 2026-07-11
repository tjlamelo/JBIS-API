<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Exceptions\ApplicationStepReopenException;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use Illuminate\Support\Facades\DB;

final class ReopenApplicationStepAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
    ) {}

    public function execute(ApplicationStep $targetStep, ?int $staffUserId = null): Application
    {
        return DB::transaction(function () use ($targetStep, $staffUserId): Application {
            $targetStep = ApplicationStep::query()->whereKey($targetStep->id)->lockForUpdate()->firstOrFail();

            $application = Application::query()->whereKey($targetStep->application_id)->lockForUpdate()->firstOrFail();

            if (! in_array($application->status, [ApplicationStatus::InProgress, ApplicationStatus::Approved], true)) {
                throw ApplicationStepReopenException::applicationNotActive();
            }

            $currentStep = $application->current_application_step_id !== null
                ? ApplicationStep::query()->whereKey($application->current_application_step_id)->first()
                : ApplicationStep::query()
                    ->where('application_id', $application->id)
                    ->where('status', ApplicationStepStatus::Completed)
                    ->orderByDesc('step_order')
                    ->first();

            $maxReachedOrder = max(
                $currentStep?->step_order ?? 0,
                (int) ApplicationStep::query()
                    ->where('application_id', $application->id)
                    ->where('status', ApplicationStepStatus::Completed)
                    ->max('step_order'),
            );

            if ($targetStep->step_order > $maxReachedOrder) {
                throw ApplicationStepReopenException::notReachable();
            }

            if (
                $application->current_application_step_id === $targetStep->id
                && $targetStep->status === ApplicationStepStatus::Pending
            ) {
                return $application->fresh(['currentStep', 'steps']);
            }

            ApplicationStep::query()
                ->where('application_id', $application->id)
                ->where('step_order', '>', $targetStep->step_order)
                ->update([
                    'status' => ApplicationStepStatus::Locked->value,
                    'completed_at' => null,
                ]);

            $targetStep->update([
                'status' => ApplicationStepStatus::Pending->value,
                'completed_at' => null,
            ]);

            $application->update([
                'current_application_step_id' => $targetStep->id,
                'status' => ApplicationStatus::InProgress->value,
            ]);

            $this->activityLogger->log(
                $application->id,
                ApplicationActivityLogger::ACTION_STEP_REOPENED,
                $targetStep->id,
                $staffUserId,
                ['step_order' => $targetStep->step_order],
            );

            return $application->fresh(['currentStep', 'steps']);
        });
    }
}
