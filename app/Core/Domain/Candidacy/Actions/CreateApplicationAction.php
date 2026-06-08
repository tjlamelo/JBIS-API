<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Exceptions\ApplicationEnrollmentException;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\ApplicationStepSnapshotBuilder;
use App\Core\Domain\Candidacy\Services\OfferApplicationReadinessService;
use App\Core\Domain\Candidacy\Services\PublishedProcessFlowResolver;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Finance\Models\PaymentInstallment;
use App\Core\Domain\Finance\Models\PaymentSchedule;
use App\Core\Domain\Workflow\States\ProcessStepType;
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
    ) {}

    public function execute(
        User $user,
        ?int $offerId,
        ?int $programId,
        ?int $countryId = null,
        ?int $processFlowId = null,
    ): Application {
        if ($offerId === null && $programId === null && $processFlowId === null) {
            throw ApplicationEnrollmentException::missingTarget();
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
            $readiness = $this->offerReadiness->assess($offer, $user);

            if (! $readiness->can_apply) {
                throw ApplicationEnrollmentException::notEligible($readiness->blocking_reasons);
            }

            $initialStatus = ApplicationStatus::tryFrom($readiness->recommended_application_status)
                ?? ApplicationStatus::InProgress;
        }

        $now = Carbon::now();

        return DB::transaction(function () use ($user, $offerId, $programId, $flow, $now, $initialStatus): Application {
            $application = Application::query()->create([
                'application_number' => $this->temporaryNumber(),
                'user_id' => $user->id,
                'offer_id' => $offerId,
                'program_id' => $programId,
                'process_flow_id' => $flow->id,
                'flow_group_id' => $flow->flow_group_id,
                'process_flow_version' => (int) $flow->version,
                'status' => $initialStatus->value,
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
            if ($first !== null && $initialStatus === ApplicationStatus::InProgress) {
                ApplicationStep::query()
                    ->whereKey($first->id)
                    ->update([
                        'status' => ApplicationStepStatus::Pending->value,
                        'updated_at' => $now,
                    ]);

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
                $type = $step->step_type instanceof ProcessStepType
                    ? $step->step_type
                    : ProcessStepType::tryFrom((string) $step->step_type);

                if ($type === ProcessStepType::Payment && (float) $step->amount_due > 0) {
                    PaymentInstallment::query()->create([
                        'application_id' => $application->id,
                        'application_step_id' => $step->id,
                        'amount' => $step->amount_due,
                        'currency' => 'XAF',
                        'status' => 'PENDING',
                    ]);
                }
            }

            if ($offerId !== null) {
                $this->attachOfferDocuments->execute($application->fresh(), $user);
            }

            return $application->fresh([
                'steps' => fn ($q) => $q->orderBy('step_order'),
                'currentStep',
                'processFlow:id,version,flow_group_id,name',
                'offer:id,title',
                'program:id,name',
                'documents.userDocument.documentType',
            ]);
        });
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
