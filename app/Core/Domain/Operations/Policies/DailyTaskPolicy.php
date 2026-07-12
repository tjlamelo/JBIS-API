<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Operations\Models\DailyTask;
use App\Core\Domain\Operations\Policies\Concerns\ChecksStaffOperationsAccess;

final class DailyTaskPolicy
{
    use ChecksStaffOperationsAccess;

    protected function resourceKey(): string
    {
        return 'dailytask';
    }

    public function view(User $user, mixed $model): bool
    {
        if ($model instanceof DailyTask && (int) $model->user_id === (int) $user->id) {
            return true;
        }

        return $this->can($user, 'view');
    }

    public function update(User $user, mixed $model): bool
    {
        if ($model instanceof DailyTask && (int) $model->user_id === (int) $user->id) {
            return true;
        }

        return $this->can($user, 'update');
    }

    public function delete(User $user, mixed $model): bool
    {
        if ($model instanceof DailyTask && (int) $model->user_id === (int) $user->id) {
            return true;
        }

        return $this->can($user, 'delete');
    }
}
