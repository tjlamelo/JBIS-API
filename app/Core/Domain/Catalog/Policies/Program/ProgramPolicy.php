<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Policies\Program;

use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;

final class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('program', ApplicationPermission::VIEW));
    }

    public function view(User $user, Program $program): bool
    {
        return $user->can(ApplicationPermission::name('program', ApplicationPermission::VIEW));
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('program', ApplicationPermission::CREATE));
    }

    public function update(User $user, Program $program): bool
    {
        return $user->can(ApplicationPermission::name('program', ApplicationPermission::UPDATE));
    }

    public function delete(User $user, Program $program): bool
    {
        return $user->can(ApplicationPermission::name('program', ApplicationPermission::DELETE));
    }

    public function uploadMedia(User $user): bool
    {
        return $this->create($user) || $user->can(ApplicationPermission::name('program', ApplicationPermission::UPDATE));
    }
}
