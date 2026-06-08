<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Recruiter\Enums\RecruiterSubmissionStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;

final class SubmitRecruiterProfileAction
{
    public function execute(RecruiterProfileSubmission $submission): RecruiterProfileSubmission
    {
        if (! $submission->isEditableByRecruiter()) {
            throw new \InvalidArgumentException(__('Cette soumission ne peut plus être modifiée.'));
        }

        $profile = $submission->candidate?->profile;
        if ($profile === null || $profile->first_name === null || $profile->last_name === null) {
            throw new \InvalidArgumentException(__('Le profil candidat doit au minimum inclure le nom et le prénom.'));
        }

        $submission->status = RecruiterSubmissionStatus::Submitted;
        $submission->submitted_at = now();
        $submission->rejection_reason = null;
        $submission->save();

        return $submission->fresh(['candidate.profile', 'organization', 'submittedBy']);
    }
}
