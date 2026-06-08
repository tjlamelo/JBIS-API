<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Actions\Profile\ModerateUserProfileAction;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterSubmissionStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;

final class ReviewRecruiterSubmissionAction
{
    public function __construct(private readonly ModerateUserProfileAction $moderateProfile) {}

    /**
     * @param  array{decision: string, staff_note?: string|null, rejection_reason?: string|null}  $payload
     */
    public function execute(RecruiterProfileSubmission $submission, User $reviewer, array $payload): RecruiterProfileSubmission
    {
        $decision = (string) ($payload['decision'] ?? '');
        $status = match ($decision) {
            'approve' => RecruiterSubmissionStatus::Approved,
            'reject' => RecruiterSubmissionStatus::Rejected,
            'needs_changes' => RecruiterSubmissionStatus::NeedsChanges,
            'in_review' => RecruiterSubmissionStatus::InReview,
            default => throw new \InvalidArgumentException(__('Décision de modération invalide.')),
        };

        $submission->status = $status;
        $submission->reviewed_by = $reviewer->id;
        $submission->reviewed_at = now();
        $submission->staff_note = $payload['staff_note'] ?? null;
        $submission->rejection_reason = $payload['rejection_reason'] ?? null;
        $submission->save();

        $candidate = $submission->candidate;
        if ($candidate !== null && $status === RecruiterSubmissionStatus::Approved) {
            $this->moderateProfile->execute($candidate, true, $reviewer->id);
        }

        if ($candidate !== null && in_array($status, [RecruiterSubmissionStatus::Rejected, RecruiterSubmissionStatus::NeedsChanges], true)) {
            $this->moderateProfile->execute($candidate, false, $reviewer->id);
        }

        return $submission->fresh(['candidate.profile', 'reviewer:id,name', 'organization']);
    }
}
