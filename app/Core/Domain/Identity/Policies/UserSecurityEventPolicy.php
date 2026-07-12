<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserSecurityEvent;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Identity\Support\ApplicationRole;

final class UserSecurityEventPolicy
{
    private function isStaff(User $user): bool
    {
        return $user->hasAnyRole(ApplicationRole::STAFF_ROLES);
    }

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user)
            || $user->can(ApplicationPermission::name('usersecurityevent', ApplicationPermission::VIEW));
    }

    public function view(User $user, UserSecurityEvent $event): bool
    {
        return $this->viewAny($user);
    }
}
