<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;

final class UpdateRecruiterOfferSubmissionAction
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(RecruiterOfferSubmission $submission, array $payload): RecruiterOfferSubmission
    {
        if (! $submission->isEditableByRecruiter()) {
            throw new \InvalidArgumentException(__('Cette offre ne peut plus être modifiée.'));
        }

        $submission->payload = array_merge($submission->payload ?? [], $payload);
        $submission->save();

        return $submission->fresh(['organization', 'submittedBy']);
    }
}
