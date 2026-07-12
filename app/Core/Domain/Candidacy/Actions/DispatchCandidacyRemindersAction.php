<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationDocument;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\CandidacyNotificationService;
use App\Core\Domain\Candidacy\Services\OfferApplicationReadinessService;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Finance\Models\PaymentInstallment;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Support\Carbon;

final class DispatchCandidacyRemindersAction
{
    public function __construct(
        private readonly CandidacyNotificationService $notifications,
        private readonly OfferApplicationReadinessService $readiness,
    ) {}

    /**
     * @return array{documents: int, payments: int}
     */
    public function execute(?Carbon $today = null, ?string $only = null): array
    {
        $today = ($today ?? Carbon::now('Africa/Douala'))->startOfDay();
        $documents = 0;
        $payments = 0;

        if ($only === null || $only === 'documents') {
            $documents = $this->remindMissingDocuments() + $this->remindDocumentSteps();
        }

        if ($only === null || $only === 'payments') {
            $payments = $this->remindPayments($today);
        }

        return [
            'documents' => $documents,
            'payments' => $payments,
        ];
    }

    private function remindMissingDocuments(): int
    {
        $count = 0;

        $applications = Application::query()
            ->with(['user:id,name,email', 'offer.requiredDocuments'])
            ->whereIn('status', [
                ApplicationStatus::Pending->value,
                ApplicationStatus::InProgress->value,
            ])
            ->whereNotNull('offer_id')
            ->get();

        foreach ($applications as $application) {
            $user = $application->user;
            $offer = $application->offer;
            if (! $user instanceof User || ! $offer instanceof Offer) {
                continue;
            }

            $readiness = $this->readiness->assess($offer, $user, true);
            $missing = [];
            foreach ($readiness->required_documents as $check) {
                if (($check['is_mandatory'] ?? false) && ! ($check['satisfied'] ?? false)) {
                    $missing[] = (string) ($check['name'] ?? __('Document'));
                }
            }

            if ($missing === []) {
                continue;
            }

            $this->notifications->missingDocumentsReminder($application, $user, $missing);
            $count++;
        }

        $revisionDocs = ApplicationDocument::query()
            ->with(['application.user:id,name,email', 'userDocument.documentType'])
            ->where('status', 'REVISION_REQUIRED')
            ->whereHas('application', function ($q): void {
                $q->whereIn('status', [
                    ApplicationStatus::Pending->value,
                    ApplicationStatus::InProgress->value,
                ]);
            })
            ->get()
            ->groupBy('application_id');

        foreach ($revisionDocs as $docs) {
            /** @var ApplicationDocument $first */
            $first = $docs->first();
            $application = $first->application;
            $user = $application?->user;
            if ($application === null || $user === null) {
                continue;
            }

            $names = $docs->map(function (ApplicationDocument $doc): string {
                return (string) (
                    \App\Core\Application\Api\Support\TranslatableColumnResolver::resolve(
                        $doc->userDocument?->documentType?->label ?? null,
                    ) ?: ($doc->userDocument?->original_filename ?? __('Document'))
                );
            })->unique()->values()->all();

            $this->notifications->missingDocumentsReminder($application, $user, $names);
            $count++;
        }

        return $count;
    }

    private function remindDocumentSteps(): int
    {
        $count = 0;

        $steps = ApplicationStep::query()
            ->with(['application.user:id,name,email'])
            ->where('status', ApplicationStepStatus::Pending->value)
            ->where('requires_documents', true)
            ->where(function ($q): void {
                $q->where('documents_validated', false)->orWhereNull('documents_validated');
            })
            ->where(function ($q): void {
                $q->where('step_type', ProcessStepType::DocumentCollection->value)
                    ->orWhere('step_type', 'DOCUMENT_COLLECTION');
            })
            ->whereHas('application', function ($q): void {
                $q->whereIn('status', [
                    ApplicationStatus::Pending->value,
                    ApplicationStatus::InProgress->value,
                ]);
            })
            ->get();

        foreach ($steps as $step) {
            $application = $step->application;
            if ($application === null || $application->user === null) {
                continue;
            }

            $this->notifications->documentStepReminder($application, $step);
            $count++;
        }

        return $count;
    }

    private function remindPayments(Carbon $today): int
    {
        $count = 0;
        $horizon = $today->copy()->addDays(3)->endOfDay();

        $installments = PaymentInstallment::query()
            ->with(['application.user:id,name,email', 'applicationStep'])
            ->where('status', 'PENDING')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $horizon->toDateString())
            ->whereHas('application', function ($q): void {
                $q->whereIn('status', [
                    ApplicationStatus::Pending->value,
                    ApplicationStatus::InProgress->value,
                ]);
            })
            ->get();

        foreach ($installments as $installment) {
            $user = $installment->application?->user;
            if ($user === null) {
                continue;
            }

            $dueDate = $installment->due_date?->copy()->timezone('Africa/Douala')->startOfDay();
            $overdue = $dueDate !== null && $dueDate->lt($today);
            $this->notifications->paymentDue($installment, $user, $overdue);
            $count++;
        }

        return $count;
    }
}
