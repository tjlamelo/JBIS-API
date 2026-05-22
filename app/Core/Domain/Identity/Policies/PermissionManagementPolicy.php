<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Policies;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;

/** Gestion des rôles / permissions (sans modèle Eloquent dédié). */
final class PermissionManagementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::PERMISSION_MANAGE);
    }

    public function update(User $user): bool
    {
        return $user->can(ApplicationPermission::PERMISSION_MANAGE);
    }
}
