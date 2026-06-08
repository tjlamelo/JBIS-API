<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use Illuminate\Support\Facades\DB;

final class AssignProfileToRecruiterAction
{
    public function execute(RecruiterOrganization $organization, User $candidate, User $assignedBy, ?string $note = null): RecruiterProfileAssignment
    {
        $profile = $candidate->profile;
        if ($profile === null || ! $profile->is_approved) {
            throw new \InvalidArgumentException(__('Seuls les profils candidats approuvés peuvent être assignés.'));
        }

        return DB::transaction(function () use ($organization, $candidate, $assignedBy, $note): RecruiterProfileAssignment {
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
                'status' => RecruiterAssignmentStatus::Active,
                'note' => $note,
                'assigned_at' => now(),
            ]);
        });
    }
}
