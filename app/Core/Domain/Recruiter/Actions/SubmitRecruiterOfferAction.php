<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Recruiter\Enums\RecruiterOfferSubmissionStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;

final class SubmitRecruiterOfferAction
{
    public function execute(RecruiterOfferSubmission $submission): RecruiterOfferSubmission
    {
        if (! $submission->isEditableByRecruiter()) {
            throw new \InvalidArgumentException(__('Cette offre ne peut plus être modifiée.'));
        }

        $title = $submission->payload['title'] ?? null;
        if (! is_array($title) || empty($title['fr'] ?? $title['en'] ?? null)) {
            throw new \InvalidArgumentException(__('Le titre de l\'offre est requis.'));
        }

        $submission->status = RecruiterOfferSubmissionStatus::Submitted;
        $submission->submitted_at = now();
        $submission->rejection_reason = null;
        $submission->save();

        return $submission->fresh(['organization', 'submittedBy']);
    }
}
