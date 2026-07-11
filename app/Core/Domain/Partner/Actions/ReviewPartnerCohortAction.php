<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Partner\Enums\PartnerCohortStatus;
use App\Core\Domain\Partner\Models\PartnerCohort;

final class ReviewPartnerCohortAction
{
    public function execute(PartnerCohort $cohort, User $reviewer, string $decision, ?string $staffNote = null, ?string $rejectionReason = null): PartnerCohort
    {
        $status = match ($decision) {
            'approve' => PartnerCohortStatus::Active,
            'reject' => PartnerCohortStatus::Rejected,
            'under_review' => PartnerCohortStatus::UnderReview,
            'close' => PartnerCohortStatus::Closed,
            default => abort(422, __('Décision invalide.')),
        };

        $cohort->update([
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'staff_note' => $staffNote,
            'rejection_reason' => $status === PartnerCohortStatus::Rejected ? $rejectionReason : null,
        ]);

        return $cohort->fresh(['organization', 'students', 'requiredDocuments']);
    }
}
