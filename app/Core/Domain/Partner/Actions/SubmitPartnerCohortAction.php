<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Partner\Enums\PartnerCohortStatus;
use App\Core\Domain\Partner\Models\PartnerCohort;

final class SubmitPartnerCohortAction
{
    public function execute(PartnerCohort $cohort): PartnerCohort
    {
        if (! $cohort->isEditableByPartner()) {
            abort(422, __('Cette cohorte ne peut pas être soumise dans son état actuel.'));
        }

        if ($cohort->students()->count() === 0) {
            abort(422, __('Ajoutez au moins un étudiant avant de soumettre la cohorte.'));
        }

        $cohort->update([
            'status' => PartnerCohortStatus::Submitted,
            'submitted_at' => now(),
            'submitted_by_user_id' => auth()->id(),
            'rejection_reason' => null,
        ]);

        return $cohort->fresh(['organization', 'students', 'requiredDocuments']);
    }
}
