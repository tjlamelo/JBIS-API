<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;

final class RecruiterProfileRequestPolicy
{
    public function __construct(private readonly RecruiterAccess $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiterprofilerequest', ApplicationPermission::VIEW));
    }

    public function view(User $user, RecruiterProfileRequest $request): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $request->recruiter_organization_id);
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiterprofilerequest', ApplicationPermission::CREATE))
            && $this->access->primaryOrganization($user) !== null;
    }

    public function update(User $user, RecruiterProfileRequest $request): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)
            && $user->can(ApplicationPermission::name('recruiterprofilerequest', ApplicationPermission::UPDATE))) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $request->recruiter_organization_id)
            && $request->isEditableByRecruiter();
    }

    public function transmit(User $user, RecruiterProfileRequest $request): bool
    {
        return $user->can(ApplicationPermission::ADMIN_ACCESS)
            && $user->can(ApplicationPermission::name('recruiterprofilerequest', ApplicationPermission::UPDATE));
    }

    public function review(User $user, RecruiterProfileRequest $request): bool
    {
        return $user->can(ApplicationPermission::ADMIN_ACCESS)
            && $user->can(ApplicationPermission::name('recruiterprofilerequest', ApplicationPermission::UPDATE));
    }
}
