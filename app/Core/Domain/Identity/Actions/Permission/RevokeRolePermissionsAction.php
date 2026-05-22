<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Permission;

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RevokeRolePermissionsAction
{
    public function __construct(
        private readonly PermissionRegistrar $registrar,
    ) {}

    /**
     * @param  list<string>  $permissionNames
     */
    public function execute(Role $role, array $permissionNames): Role
    {
        foreach ($permissionNames as $name) {
            $role->revokePermissionTo($name);
        }
        $this->registrar->forgetCachedPermissions();

        return $role->load('permissions');
    }
}
