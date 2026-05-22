<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Workflow\Models\ProcessStep;

final class ProcessStepPolicy
{
    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('processstep', ApplicationPermission::CREATE));
    }

    public function update(User $user, ProcessStep $processStep): bool
    {
        return $user->can(ApplicationPermission::name('processstep', ApplicationPermission::UPDATE));
    }

    public function delete(User $user, ProcessStep $processStep): bool
    {
        return $user->can(ApplicationPermission::name('processstep', ApplicationPermission::DELETE));
    }
}
