<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Operations\Models\Meeting;
use App\Core\Domain\Operations\Policies\Concerns\ChecksStaffOperationsAccess;
use App\Core\Domain\Operations\Support\OperationsAccess;

final class MeetingPolicy
{
    use ChecksStaffOperationsAccess;

    protected function resourceKey(): string
    {
        return 'meeting';
    }

    public function create(User $user): bool
    {
        return OperationsAccess::canCreateMeeting($user);
    }

    public function update(User $user, mixed $model): bool
    {
        if (! $model instanceof Meeting) {
            return $this->can($user, ApplicationPermission::UPDATE);
        }

        if (OperationsAccess::isAdmin($user) || OperationsAccess::canManageMeetings($user)) {
            return true;
        }

        return (int) $model->organizer_id === (int) $user->id;
    }

    public function delete(User $user, mixed $model): bool
    {
        if (! $model instanceof Meeting) {
            return $this->can($user, ApplicationPermission::DELETE);
        }

        if (OperationsAccess::isAdmin($user) || OperationsAccess::canManageMeetings($user)) {
            return true;
        }

        return (int) $model->organizer_id === (int) $user->id;
    }
}
