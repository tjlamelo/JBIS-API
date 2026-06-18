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

        $tradeId = $submission->payload['trade_id'] ?? null;
        if (! is_numeric($tradeId) || (int) $tradeId <= 0) {
            throw new \InvalidArgumentException(__('Le métier de l\'offre est requis.'));
        }

        $submission->status = RecruiterOfferSubmissionStatus::Submitted;
        $submission->submitted_at = now();
        $submission->rejection_reason = null;
        $submission->save();

        return $submission->fresh(['organization', 'submittedBy']);
    }
}
