<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;

final class RevokeRecruiterAssignmentAction
{
    public function execute(RecruiterProfileAssignment $assignment): RecruiterProfileAssignment
    {
        if ($assignment->status === RecruiterAssignmentStatus::Revoked) {
            return $assignment;
        }

        $assignment->status = RecruiterAssignmentStatus::Revoked;
        $assignment->revoked_at = now();
        $assignment->save();

        return $assignment;
    }
}
