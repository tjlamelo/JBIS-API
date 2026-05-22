<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Policies\Training;

use App\Core\Domain\Catalog\Models\Training;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;

final class TrainingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('training', ApplicationPermission::VIEW));
    }

    public function view(User $user, Training $training): bool
    {
        return $user->can(ApplicationPermission::name('training', ApplicationPermission::VIEW));
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('training', ApplicationPermission::CREATE));
    }

    public function update(User $user, Training $training): bool
    {
        return $user->can(ApplicationPermission::name('training', ApplicationPermission::UPDATE));
    }

    public function delete(User $user, Training $training): bool
    {
        return $user->can(ApplicationPermission::name('training', ApplicationPermission::DELETE));
    }
}
