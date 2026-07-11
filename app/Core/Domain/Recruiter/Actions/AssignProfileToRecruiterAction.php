<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Enums\RecruiterMaskedField;
use App\Core\Domain\Recruiter\Enums\RecruiterSharedProfileSection;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use Illuminate\Support\Facades\DB;

final class AssignProfileToRecruiterAction
{
    /**
     * @param  list<string>|null  $visibleSections
     * @param  list<string>|null  $maskedFields
     */
    public function execute(
        RecruiterOrganization $organization,
        User $candidate,
        User $assignedBy,
        ?string $note = null,
        ?array $visibleSections = null,
        ?array $maskedFields = null,
        ?int $profileRequestId = null,
    ): RecruiterProfileAssignment {
        $profile = $candidate->profile;
        if ($profile === null || ! $profile->is_approved) {
            throw new \InvalidArgumentException(__('Seuls les profils candidats approuvés peuvent être assignés.'));
        }

        return DB::transaction(function () use ($organization, $candidate, $assignedBy, $note, $visibleSections, $maskedFields, $profileRequestId): RecruiterProfileAssignment {
            RecruiterProfileAssignment::query()
                ->where('recruiter_organization_id', $organization->id)
                ->where('candidate_user_id', $candidate->id)
                ->where('status', RecruiterAssignmentStatus::Active)
                ->update([
                    'status' => RecruiterAssignmentStatus::Revoked,
                    'revoked_at' => now(),
                ]);

            return RecruiterProfileAssignment::query()->create([
                'recruiter_organization_id' => $organization->id,
                'candidate_user_id' => $candidate->id,
                'assigned_by_user_id' => $assignedBy->id,
                'recruiter_profile_request_id' => $profileRequestId,
                'status' => RecruiterAssignmentStatus::Active,
                'note' => $note,
                'visible_sections' => RecruiterSharedProfileSection::normalize($visibleSections),
                'masked_fields' => RecruiterMaskedField::normalize($maskedFields),
                'assigned_at' => now(),
            ]);
        });
    }
}
