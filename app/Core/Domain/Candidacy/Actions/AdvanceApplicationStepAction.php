<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Candidacy\Exceptions\ApplicationStepAdvanceException;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\States\ApplicationStepPaymentStatus;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class AdvanceApplicationStepAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
    ) {}

    public function execute(ApplicationStep $step, ?int $staffUserId = null, bool $force = false): Application
    {
        return DB::transaction(function () use ($step, $staffUserId, $force): Application {
            $step = ApplicationStep::query()->whereKey($step->id)->lockForUpdate()->firstOrFail();

            if ($step->status !== ApplicationStepStatus::Pending) {
                throw ApplicationStepAdvanceException::notPending();
            }

            if (! $force) {
                $this->assertPrerequisites($step);
            }

            $now = Carbon::now();
            $step->update([
                'status' => ApplicationStepStatus::Completed->value,
                'completed_at' => $now,
            ]);

            $application = Application::query()->whereKey($step->application_id)->lockForUpdate()->firstOrFail();

            $next = ApplicationStep::query()
                ->where('application_id', $application->id)
                ->where('step_order', '>', $step->step_order)
                ->orderBy('step_order')
                ->first();

            if ($next !== null) {
                $next->update(['status' => ApplicationStepStatus::Pending->value]);
                $application->update([
                    'current_application_step_id' => $next->id,
                    'status' => ApplicationStatus::InProgress->value,
                ]);
            } else {
                $application->update([
                    'current_application_step_id' => null,
                    'status' => ApplicationStatus::Approved->value,
                ]);
            }

            $this->activityLogger->log(
                $application->id,
                ApplicationActivityLogger::ACTION_STEP_ADVANCED,
                $step->id,
                $staffUserId,
                ['force' => $force, 'next_step_id' => $next?->id],
            );

            return $application->fresh(['currentStep', 'steps']);
        });
    }

    private function assertPrerequisites(ApplicationStep $step): void
    {
        $type = $step->step_type instanceof ProcessStepType
            ? $step->step_type
            : ProcessStepType::tryFrom((string) $step->step_type);

        if ($type === ProcessStepType::Payment || $type?->value === 'PAYMENT' || (float) $step->amount_due > 0) {
            $paid = $step->payment_status === ApplicationStepPaymentStatus::Paid
                || $step->payment_status === ApplicationStepPaymentStatus::Waived
                || $step->payment_status === ApplicationStepPaymentStatus::Overpaid;

            if (! $paid) {
                throw ApplicationStepAdvanceException::paymentRequired();
            }
        }

        if ($type === ProcessStepType::DocumentCollection || $type?->value === 'DOCUMENT_COLLECTION') {
            if ($step->requires_documents && ! $step->documents_validated) {
                throw ApplicationStepAdvanceException::documentsNotValidated();
            }
        }

        if ($type === ProcessStepType::Interview || $type?->value === 'INTERVIEW') {
            if ($step->interview_passed !== true) {
                throw ApplicationStepAdvanceException::interviewNotPassed();
            }
        }

        if ($type === ProcessStepType::Signing || $type?->value === 'SIGNING') {
            if (! $step->is_signed) {
                throw ApplicationStepAdvanceException::signatureRequired();
            }
        }
    }
}
