<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Services;

use App\Core\Domain\Candidacy\Models\ApplicationStepEvent;
use Illuminate\Support\Carbon;

final class ApplicationActivityLogger
{
    public const ACTION_STEP_ADVANCED = 'step.advanced';

    public const ACTION_STEP_REOPENED = 'step.reopened';

    public const ACTION_PAYMENT_RECORDED = 'payment.recorded';

    public const ACTION_DOCUMENTS_VALIDATED = 'step.documents_validated';

    public const ACTION_INTERVIEW_VALIDATED = 'step.interview_validated';

    public const ACTION_INTERVIEW_UPDATED = 'interview.updated';

    public const ACTION_SIGNATURE_VALIDATED = 'step.signature_validated';

    public const ACTION_DOCUMENT_ATTACHED = 'document.attached';

    public const ACTION_DOCUMENT_REVIEWED = 'document.reviewed';

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function log(
        int $applicationId,
        string $action,
        ?int $applicationStepId = null,
        ?int $actorUserId = null,
        ?array $meta = null,
    ): ApplicationStepEvent {
        return ApplicationStepEvent::query()->create([
            'application_id' => $applicationId,
            'application_step_id' => $applicationStepId,
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'meta' => $meta,
            'created_at' => Carbon::now(),
        ]);
    }
}
