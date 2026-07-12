<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Services;

use App\Core\Application\Api\Support\TranslatableColumnResolver;
use App\Core\Application\Mail\Jobs\SendCandidacyAlertMailJob;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationDocument;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Communication\Enums\InAppNotificationType;
use App\Core\Domain\Communication\Services\InAppNotificationService;
use App\Core\Domain\Finance\Models\PaymentInstallment;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserSetting;

final class CandidacyNotificationService
{
    public function __construct(
        private readonly InAppNotificationService $notifications,
    ) {}

    public function applicationSubmitted(Application $application): void
    {
        $application->loadMissing(['user:id,name,email', 'offer', 'program', 'currentStep']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $status = $this->statusValue($application);
        $ref = $application->application_number ?: (string) $application->id;

        if ($status === ApplicationStatus::Pending->value) {
            $this->pushCandidate(
                $user,
                InAppNotificationType::ApplicationSubmitted,
                __('notifications.application_submitted_pending.title'),
                __('notifications.application_submitted_pending.body', ['ref' => $ref]),
                ['application_id' => $application->id, 'status' => $status],
                "application_submitted:{$application->id}",
                $this->candidateUrl($application->id),
            );

            return;
        }

        $stepTitle = $this->stepTitle($application->currentStep);
        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationSubmitted,
            __('notifications.application_submitted.title'),
            __('notifications.application_submitted.body', [
                'ref' => $ref,
                'step' => $stepTitle !== '' ? $stepTitle : '—',
            ]),
            ['application_id' => $application->id, 'status' => $status],
            "application_submitted:{$application->id}",
            $this->candidateUrl($application->id),
        );
    }

    public function applicationRejected(Application $application, ?string $reason = null): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $ref = $application->application_number ?: (string) $application->id;
        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationStatusChanged,
            __('notifications.application_rejected.title'),
            __('notifications.application_rejected.body', [
                'ref' => $ref,
                'reason' => $reason !== null && trim($reason) !== '' ? $reason : __('notifications.application_rejected.no_reason'),
            ]),
            ['application_id' => $application->id, 'status' => ApplicationStatus::Rejected->value],
            "application_status:{$application->id}:rejected",
            $this->candidateUrl($application->id),
        );
    }

    public function applicationCancelled(Application $application, ?string $reason = null): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $ref = $application->application_number ?: (string) $application->id;
        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationStatusChanged,
            __('notifications.application_cancelled.title'),
            __('notifications.application_cancelled.body', [
                'ref' => $ref,
                'reason' => $reason !== null && trim($reason) !== '' ? $reason : __('notifications.application_cancelled.no_reason'),
            ]),
            ['application_id' => $application->id, 'status' => ApplicationStatus::Cancelled->value],
            "application_status:{$application->id}:cancelled",
            $this->candidateUrl($application->id),
        );
    }

    public function applicationApproved(Application $application): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $ref = $application->application_number ?: (string) $application->id;
        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationStatusChanged,
            __('notifications.application_approved.title'),
            __('notifications.application_approved.body', ['ref' => $ref]),
            ['application_id' => $application->id, 'status' => ApplicationStatus::Approved->value],
            "application_status:{$application->id}:approved",
            $this->candidateUrl($application->id),
        );
    }

    public function stepPending(Application $application, ApplicationStep $step): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $ref = $application->application_number ?: (string) $application->id;
        $stepTitle = $this->stepTitle($step);

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationStepPending,
            __('notifications.application_step_pending.title'),
            __('notifications.application_step_pending.body', [
                'ref' => $ref,
                'step' => $stepTitle !== '' ? $stepTitle : '—',
            ]),
            [
                'application_id' => $application->id,
                'application_step_id' => $step->id,
                'step_type' => $step->step_type instanceof \BackedEnum
                    ? $step->step_type->value
                    : (string) $step->step_type,
            ],
            "application_step_pending:{$application->id}:{$step->id}",
            $this->candidateUrl($application->id),
        );
    }

    public function paymentDeclared(Application $application, ApplicationStep $step, float $amount): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationPaymentDeclared,
            __('notifications.application_payment_declared.title'),
            __('notifications.application_payment_declared.body', [
                'ref' => $application->application_number ?: (string) $application->id,
                'step' => $this->stepTitle($step) ?: '—',
                'amount' => number_format($amount, 0, ',', ' '),
            ]),
            ['application_id' => $application->id, 'application_step_id' => $step->id],
            "application_payment_declared:{$application->id}:{$step->id}:".now()->format('YmdHi'),
            $this->candidateUrl($application->id),
        );
    }

    public function paymentConfirmed(Application $application, ApplicationStep $step, float $amount): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationPaymentConfirmed,
            __('notifications.application_payment_confirmed.title'),
            __('notifications.application_payment_confirmed.body', [
                'ref' => $application->application_number ?: (string) $application->id,
                'step' => $this->stepTitle($step) ?: '—',
                'amount' => number_format(abs($amount), 0, ',', ' '),
            ]),
            ['application_id' => $application->id, 'application_step_id' => $step->id],
            "application_payment_confirmed:{$application->id}:{$step->id}:".now()->format('YmdHi'),
            $this->candidateUrl($application->id),
        );
    }

    public function paymentWaived(Application $application, ApplicationStep $step): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationPaymentWaived,
            __('notifications.application_payment_waived.title'),
            __('notifications.application_payment_waived.body', [
                'ref' => $application->application_number ?: (string) $application->id,
                'step' => $this->stepTitle($step) ?: '—',
            ]),
            ['application_id' => $application->id, 'application_step_id' => $step->id],
            "application_payment_waived:{$application->id}:{$step->id}",
            $this->candidateUrl($application->id),
        );
    }

    public function paymentDue(PaymentInstallment $installment, User $user, bool $overdue): void
    {
        $installment->loadMissing(['application', 'applicationStep']);
        $application = $installment->application;
        if ($application === null) {
            return;
        }

        $due = $installment->due_date?->timezone('Africa/Douala')->format('d/m/Y') ?? '—';
        $amount = number_format((float) $installment->amount, 0, ',', ' ');
        $stepTitle = $this->stepTitle($installment->applicationStep);

        $title = $overdue
            ? __('notifications.application_payment_overdue.title')
            : __('notifications.application_payment_due.title');
        $body = $overdue
            ? __('notifications.application_payment_overdue.body', [
                'ref' => $application->application_number ?: (string) $application->id,
                'amount' => $amount,
                'due' => $due,
                'step' => $stepTitle ?: '—',
            ])
            : __('notifications.application_payment_due.body', [
                'ref' => $application->application_number ?: (string) $application->id,
                'amount' => $amount,
                'due' => $due,
                'step' => $stepTitle ?: '—',
            ]);

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationPaymentDue,
            $title,
            $body,
            [
                'application_id' => $application->id,
                'payment_installment_id' => $installment->id,
                'overdue' => $overdue,
            ],
            'application_payment_due:'.$installment->id.':'.now()->toDateString(),
            $this->candidateUrl((int) $application->id),
        );
    }

    public function documentReviewed(ApplicationDocument $document): void
    {
        $document->loadMissing(['application.user:id,name,email', 'userDocument.documentType']);
        $application = $document->application;
        $user = $application?->user;
        if ($application === null || $user === null) {
            return;
        }

        $status = strtoupper((string) $document->status);
        $docName = TranslatableColumnResolver::resolve(
            $document->userDocument?->documentType?->label ?? null,
        ) ?: (string) ($document->userDocument?->original_filename ?? __('Document'));

        $key = match ($status) {
            'APPROVED' => 'application_document_approved',
            'REJECTED' => 'application_document_rejected',
            'REVISION_REQUIRED' => 'application_document_revision',
            default => null,
        };

        if ($key === null) {
            return;
        }

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationDocumentReviewed,
            __("notifications.{$key}.title"),
            __("notifications.{$key}.body", [
                'ref' => $application->application_number ?: (string) $application->id,
                'document' => $docName,
                'notes' => trim((string) ($document->admin_notes ?? '')) !== ''
                    ? (string) $document->admin_notes
                    : __('notifications.application_document_reviewed.no_notes'),
            ]),
            [
                'application_id' => $application->id,
                'application_document_id' => $document->id,
                'status' => $status,
            ],
            "application_document_reviewed:{$document->id}:{$status}",
            $this->candidateUrl((int) $application->id),
        );
    }

    /**
     * @param  list<string>  $documentNames
     */
    public function missingDocumentsReminder(Application $application, User $user, array $documentNames): void
    {
        if ($documentNames === []) {
            return;
        }

        $list = implode(', ', array_slice($documentNames, 0, 5));
        if (count($documentNames) > 5) {
            $list .= '…';
        }

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationDocumentReminder,
            __('notifications.application_document_reminder.title'),
            __('notifications.application_document_reminder.body', [
                'ref' => $application->application_number ?: (string) $application->id,
                'documents' => $list,
                'count' => count($documentNames),
            ]),
            [
                'application_id' => $application->id,
                'documents' => $documentNames,
            ],
            'application_document_reminder:'.$application->id.':'.now()->toDateString(),
            $this->candidateUrl((int) $application->id),
        );
    }

    public function documentStepReminder(Application $application, ApplicationStep $step): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationDocumentReminder,
            __('notifications.application_document_step_reminder.title'),
            __('notifications.application_document_step_reminder.body', [
                'ref' => $application->application_number ?: (string) $application->id,
                'step' => $this->stepTitle($step) ?: '—',
            ]),
            [
                'application_id' => $application->id,
                'application_step_id' => $step->id,
            ],
            'application_document_step_reminder:'.$step->id.':'.now()->toDateString(),
            $this->candidateUrl((int) $application->id),
        );
    }

    public function protocolAccepted(Application $application): void
    {
        $application->loadMissing(['user:id,name,email']);
        $user = $application->user;
        if ($user === null) {
            return;
        }

        $this->pushCandidate(
            $user,
            InAppNotificationType::ApplicationProtocolAccepted,
            __('notifications.application_protocol_accepted.title'),
            __('notifications.application_protocol_accepted.body', [
                'ref' => $application->application_number ?: (string) $application->id,
            ]),
            ['application_id' => $application->id],
            "application_protocol_accepted:{$application->id}",
            $this->candidateUrl((int) $application->id),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function pushCandidate(
        User $user,
        InAppNotificationType $type,
        string $title,
        string $body,
        array $data,
        string $dedupeKey,
        string $actionUrl,
    ): void {
        $this->notifications->notify($user, $type, $title, $body, $data, $dedupeKey, $actionUrl);

        if ($user->email && $this->wantsApplicationEmail($user)) {
            SendCandidacyAlertMailJob::dispatch(
                userId: (int) $user->id,
                subject: $title,
                body: $body,
                actionUrl: $actionUrl,
            )->onQueue('mail');
        }
    }

    private function wantsApplicationEmail(User $user): bool
    {
        $user->loadMissing('settings');
        $notifications = $user->settings?->notifications ?? UserSetting::defaultNotifications();

        return (bool) data_get($notifications, 'email.applications', true);
    }

    private function candidateUrl(int $applicationId): string
    {
        return "/candidate/applications/{$applicationId}";
    }

    private function statusValue(Application $application): string
    {
        return $application->status instanceof ApplicationStatus
            ? $application->status->value
            : (string) $application->status;
    }

    private function stepTitle(?ApplicationStep $step): string
    {
        if ($step === null) {
            return '';
        }

        return TranslatableColumnResolver::resolve($step->title);
    }
}
