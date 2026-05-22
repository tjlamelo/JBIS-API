<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::VIEW));
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::VIEW))
            || (int) $user->id === (int) $model->id;
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::CREATE));
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::UPDATE));
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::DELETE));
    }

    public function moderateProfile(User $user, User $model): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::UPDATE));
    }

    public function managePermissionOverrides(User $user, User $model): bool
    {
        return $user->can(ApplicationPermission::PERMISSION_MANAGE);
    }
}
