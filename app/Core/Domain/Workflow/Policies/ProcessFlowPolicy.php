<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Workflow\Models\ProcessFlow;

final class ProcessFlowPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('processflow', ApplicationPermission::VIEW));
    }

    public function view(User $user, ProcessFlow $processFlow): bool
    {
        return $user->can(ApplicationPermission::name('processflow', ApplicationPermission::VIEW));
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('processflow', ApplicationPermission::CREATE));
    }

    public function update(User $user, ProcessFlow $processFlow): bool
    {
        return $user->can(ApplicationPermission::name('processflow', ApplicationPermission::UPDATE));
    }

    public function delete(User $user, ProcessFlow $processFlow): bool
    {
        return $user->can(ApplicationPermission::name('processflow', ApplicationPermission::DELETE));
    }

    public function publish(User $user, ProcessFlow $processFlow): bool
    {
        return $this->update($user, $processFlow);
    }
}
