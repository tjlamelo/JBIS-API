<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Exceptions\ApplicationEnrollmentException;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\ApplicationStepSnapshotBuilder;
use App\Core\Domain\Candidacy\Services\CandidacyNotificationService;
use App\Core\Domain\Candidacy\Services\OfferApplicationReadinessService;
use App\Core\Domain\Candidacy\Services\PublishedProcessFlowResolver;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Finance\Models\PaymentInstallment;
use App\Core\Domain\Finance\Models\PaymentSchedule;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Identity\Actions\Profile\AssignCandidateMatriculeAction;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class CreateApplicationAction
{
    public function __construct(
        private readonly PublishedProcessFlowResolver $processFlowResolver,
        private readonly ApplicationStepSnapshotBuilder $snapshotBuilder,
        private readonly OfferApplicationReadinessService $offerReadiness,
        private readonly AttachOfferRequiredDocumentsAction $attachOfferDocuments,
        private readonly AssignCandidateMatriculeAction $assignMatricule,
        private readonly CandidacyNotificationService $candidacyNotifications,
    ) {}

    public function execute(
        User $user,
        ?int $offerId,
        ?int $programId,
        ?int $countryId = null,
        ?int $processFlowId = null,
        bool $asPrivate = false,
        ?User $enrolledBy = null,
    ): Application {
        if ($offerId === null && $programId === null && $processFlowId === null) {
            throw ApplicationEnrollmentException::missingTarget();
        }

        if (! $user->hasVerifiedEmail()) {
            throw ApplicationEnrollmentException::notEligible([
                __('Vérifiez votre adresse e-mail avant de candidater.'),
            ]);
        }

        $flow = $this->processFlowResolver->resolveWithSteps($offerId, $programId, $countryId, $processFlowId);
        if ($flow === null) {
            throw ApplicationEnrollmentException::processFlowNotFound();
        }

        if ($flow->steps->isEmpty()) {
            throw ApplicationEnrollmentException::processFlowHasNoSteps();
        }

        $initialStatus = ApplicationStatus::InProgress;
        if ($offerId !== null) {
            $offer = Offer::query()
                ->with(['requiredDocuments', 'program.requiredDocuments'])
                ->findOrFail($offerId);
            $readiness = $this->offerReadiness->assess($offer, $user, $asPrivate);

            if (! $readiness->can_apply) {
                throw ApplicationEnrollmentException::notEligible($readiness->blocking_reasons);
            }

            $initialStatus = ApplicationStatus::tryFrom($readiness->recommended_application_status)
                ?? ApplicationStatus::InProgress;
        }

        $now = Carbon::now();
        $isPrivate = $asPrivate;
        $createdById = $enrolledBy?->id;

        $application = DB::transaction(function () use ($user, $offerId, $programId, $flow, $now, $initialStatus, $isPrivate, $createdById): Application {
            $this->assignMatricule->execute($user);

            $application = Application::query()->create([
                'application_number' => $this->temporaryNumber(),
                'user_id' => $user->id,
                'offer_id' => $offerId,
                'program_id' => $programId,
                'process_flow_id' => $flow->id,
                'flow_group_id' => $flow->flow_group_id,
                'process_flow_version' => (int) $flow->version,
                'status' => $initialStatus->value,
                'is_private' => $isPrivate,
                'created_by' => $createdById,
                'total_due' => 0,
                'total_paid' => 0,
            ]);

            $application->update([
                'application_number' => $this->formatNumber($application->id),
            ]);

            $rows = $this->snapshotBuilder->buildRows($application->id, $flow, $now);
            ApplicationStep::query()->insert($rows);

            $steps = ApplicationStep::query()
                ->where('application_id', $application->id)
                ->orderBy('step_order')
                ->get();

            $first = $steps->first();
            if ($first !== null) {
                if ($initialStatus === ApplicationStatus::InProgress) {
                    ApplicationStep::query()
                        ->whereKey($first->id)
                        ->update([
                            'status' => ApplicationStepStatus::Pending->value,
                            'updated_at' => $now,
                        ]);
                }

                $application->update([
                    'current_application_step_id' => $first->id,
                ]);
            }

            $totalDue = (float) $steps->sum('amount_due');

            $application->update([
                'total_due' => $totalDue,
            ]);

            PaymentSchedule::query()->create([
                'application_id' => $application->id,
                'total_amount' => $totalDue,
                'paid_amount' => 0,
            ]);

            foreach ($steps as $step) {
                if ((float) $step->amount_due <= 0) {
                    continue;
                }

                $dueDate = null;
                if ($step->process_step_id) {
                    $template = ProcessStep::query()->find($step->process_step_id);
                    $days = (int) ($template?->sla_alert_days ?? $template?->estimated_duration_days ?? 14);
                    $dueDate = $now->copy()->addDays(max(1, $days));
                }

                PaymentInstallment::query()->create([
                    'application_id' => $application->id,
                    'application_step_id' => $step->id,
                    'amount' => $step->amount_due,
                    'currency' => 'XAF',
                    'due_date' => $dueDate,
                    'status' => 'PENDING',
                ]);
            }

            if ($offerId !== null) {
                $this->attachOfferDocuments->execute($application->fresh(), $user);
            }

            return $application->fresh([
                'steps' => fn ($q) => $q->orderBy('step_order'),
                'currentStep',
                'processFlow:id,version,flow_group_id,name',
                'offer:id',
                'program:id',
                'documents.userDocument.documentType',
                'user:id,name,email',
            ]);
        });

        $this->candidacyNotifications->applicationSubmitted($application);

        return $application;
    }

    private function temporaryNumber(): string
    {
        return 'JBIS-PENDING-'.uniqid();
    }

    private function formatNumber(int $id): string
    {
        return sprintf('JBIS-%d-%05d', (int) date('Y'), $id);
    }
}
