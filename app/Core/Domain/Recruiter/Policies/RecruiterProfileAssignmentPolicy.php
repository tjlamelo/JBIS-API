<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;

final class RecruiterProfileAssignmentPolicy
{
    public function __construct(private readonly RecruiterAccess $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiterassignment', ApplicationPermission::VIEW));
    }

    public function view(User $user, RecruiterProfileAssignment $assignment): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $assignment->recruiter_organization_id);
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiterassignment', ApplicationPermission::CREATE));
    }

    public function update(User $user, RecruiterProfileAssignment $assignment): bool
    {
        return $user->can(ApplicationPermission::name('recruiterassignment', ApplicationPermission::UPDATE));
    }
}
