<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Candidacy\Services\CandidacyNotificationService;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

final class RejectApplicationAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
        private readonly CandidacyNotificationService $candidacyNotifications,
    ) {}

    public function execute(Application $application, User $actor, ?string $reason = null): Application
    {
        $status = $application->status instanceof ApplicationStatus
            ? $application->status
            : ApplicationStatus::tryFrom((string) $application->status);

        if (! in_array($status, [ApplicationStatus::Pending, ApplicationStatus::InProgress], true)) {
            throw new \InvalidArgumentException(__('Cette candidature ne peut plus être refusée.'));
        }

        $application = DB::transaction(function () use ($application, $actor, $reason): Application {
            $application->update([
                'status' => ApplicationStatus::Rejected->value,
                'current_application_step_id' => null,
            ]);

            $application->steps()->update([
                'status' => ApplicationStepStatus::Locked->value,
            ]);

            $this->activityLogger->log(
                (int) $application->id,
                'application.rejected',
                null,
                (int) $actor->id,
                ['reason' => $reason],
            );

            return $application->fresh(['steps', 'currentStep', 'offer', 'program', 'user:id,name,email']);
        });

        $this->candidacyNotifications->applicationRejected($application, $reason);

        return $application;
    }
}
