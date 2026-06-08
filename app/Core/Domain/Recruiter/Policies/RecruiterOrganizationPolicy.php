<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;

final class RecruiterOrganizationPolicy
{
    public function __construct(private readonly RecruiterAccess $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiterorganization', ApplicationPermission::VIEW));
    }

    public function view(User $user, RecruiterOrganization $organization): bool
    {
        if ($user->can(ApplicationPermission::ADMIN_ACCESS)) {
            return true;
        }

        return $this->access->belongsToOrganization($user, $organization->id);
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('recruiterorganization', ApplicationPermission::CREATE));
    }

    public function update(User $user, RecruiterOrganization $organization): bool
    {
        return $user->can(ApplicationPermission::name('recruiterorganization', ApplicationPermission::UPDATE));
    }
}
