<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Actions\Profile\UpdateAdminUserProfileWizardStepAction;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;

final class UpdateRecruiterSubmissionStepAction
{
    public function __construct(
        private readonly UpdateAdminUserProfileWizardStepAction $updateWizardStep,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(RecruiterProfileSubmission $submission, string $step, array $payload): UserProfile
    {
        if (! $submission->isEditableByRecruiter()) {
            throw new \InvalidArgumentException(__('Cette soumission ne peut plus être modifiée.'));
        }

        $candidate = $submission->candidate;
        if ($candidate === null) {
            throw new \InvalidArgumentException(__('Candidat introuvable.'));
        }

        return $this->updateWizardStep->execute($candidate, $step, $payload);
    }
}
