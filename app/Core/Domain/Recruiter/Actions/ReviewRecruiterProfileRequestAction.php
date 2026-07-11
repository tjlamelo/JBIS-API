<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterProfileRequestStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;

final class ReviewRecruiterProfileRequestAction
{
    public function execute(
        RecruiterProfileRequest $request,
        User $reviewer,
        string $decision,
        ?string $staffNote = null,
        ?string $rejectionReason = null,
    ): RecruiterProfileRequest {
        $status = match ($decision) {
            'reject' => RecruiterProfileRequestStatus::Rejected,
            'needs_changes' => RecruiterProfileRequestStatus::NeedsChanges,
            default => $request->status,
        };

        $request->status = $status;
        $request->reviewed_by = $reviewer->id;
        $request->reviewed_at = now();
        $request->staff_note = $staffNote;
        $request->rejection_reason = $rejectionReason;
        $request->save();

        return $request->fresh(['organization', 'submittedBy:id,name,email', 'reviewer:id,name']);
    }
}
