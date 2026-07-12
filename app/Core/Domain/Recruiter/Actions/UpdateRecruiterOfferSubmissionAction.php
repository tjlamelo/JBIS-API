<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Support\RecruiterOfferPayloadFields;

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

        $overlay = RecruiterOfferPayloadFields::only($payload, RecruiterOfferPayloadFields::RECRUITER_KEYS);
        $base = is_array($submission->payload) ? $submission->payload : [];
        $submission->payload = RecruiterOfferPayloadFields::merge($base, $overlay);
        $submission->save();

        return $submission->fresh(['organization', 'submittedBy']);
    }
}
