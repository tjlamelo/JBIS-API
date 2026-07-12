<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\Archive;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Identity\Support\ApplicationRole;

final class ArchivePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user) || $user->can(ApplicationPermission::name('archive', ApplicationPermission::VIEW));
    }

    public function view(User $user, Archive $archive): bool
    {
        if ($this->viewAny($user)) {
            return true;
        }

        return (int) $user->id === (int) ($archive->related_user_id ?? $archive->user_id);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user) || $user->can(ApplicationPermission::name('archive', ApplicationPermission::CREATE));
    }

    /** @deprecated Use create(); kept for AuthorizesStoreViaPolicy gate name "store". */
    public function store(User $user, User $target): bool
    {
        return $this->create($user);
    }

    public function update(User $user, Archive $archive): bool
    {
        return $this->isStaff($user) || $user->can(ApplicationPermission::name('archive', ApplicationPermission::UPDATE));
    }

    public function delete(User $user, Archive $archive): bool
    {
        return $this->isStaff($user) || $user->can(ApplicationPermission::name('archive', ApplicationPermission::DELETE));
    }

    private function isStaff(User $user): bool
    {
        foreach (ApplicationRole::STAFF_ROLES as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
