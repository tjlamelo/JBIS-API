<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Policies\Concerns;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Identity\Support\ApplicationRole;

trait ChecksStaffOperationsAccess
{
    abstract protected function resourceKey(): string;

    protected function can(User $user, string $action): bool
    {
        if ($this->isStaff($user)) {
            return true;
        }

        return $user->can(ApplicationPermission::name($this->resourceKey(), $action));
    }

    protected function isStaff(User $user): bool
    {
        return $user->hasAnyRole(ApplicationRole::STAFF_ROLES);
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, ApplicationPermission::VIEW);
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->can($user, ApplicationPermission::VIEW);
    }

    public function create(User $user): bool
    {
        return $this->can($user, ApplicationPermission::CREATE);
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->can($user, ApplicationPermission::UPDATE);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->can($user, ApplicationPermission::DELETE);
    }
}
