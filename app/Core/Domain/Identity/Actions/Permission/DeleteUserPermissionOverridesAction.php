<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Permission;

use App\Core\Domain\Identity\Models\User;

final class DeleteUserPermissionOverridesAction
{
    /**
     * @param  list<string>  $permissionNames
     */
    public function execute(User $user, array $permissionNames): void
    {
        $user->permissionOverrides()
            ->whereIn('permission_name', $permissionNames)
            ->delete();

        $user->flushPermissionOverridesCache();
    }
}
