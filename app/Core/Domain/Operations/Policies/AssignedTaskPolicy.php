<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Policies\Concerns\ChecksStaffOperationsAccess;
use App\Core\Domain\Operations\Support\OperationsAccess;

final class AssignedTaskPolicy
{
    use ChecksStaffOperationsAccess;

    protected function resourceKey(): string
    {
        return 'assignedtask';
    }

    public function view(User $user, mixed $model): bool
    {
        if (! $model instanceof AssignedTask) {
            return $this->can($user, ApplicationPermission::VIEW);
        }

        return OperationsAccess::canViewTask($user, $model);
    }

    public function update(User $user, mixed $model): bool
    {
        if (! $model instanceof AssignedTask) {
            return $this->can($user, ApplicationPermission::UPDATE);
        }

        return OperationsAccess::canUpdateTask($user, $model);
    }

    public function delete(User $user, mixed $model): bool
    {
        if (! $model instanceof AssignedTask) {
            return $this->can($user, ApplicationPermission::DELETE);
        }

        if (OperationsAccess::canViewAllTasks($user) || OperationsAccess::canManageMeetings($user)) {
            return true;
        }

        return (int) $model->created_by === (int) $user->id;
    }
}
