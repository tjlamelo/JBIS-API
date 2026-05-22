<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Education;

use App\Core\Domain\Identity\Models\Education;

final class ApproveEducationAction
{
    public function execute(Education $education, int $approverId, bool $isApproved): Education
    {
        $education->is_approved = $isApproved;
        $education->approved_by = $isApproved ? $approverId : null;
        $education->save();

        return $education->fresh(['level', 'country', 'document', 'user']);
    }
}
