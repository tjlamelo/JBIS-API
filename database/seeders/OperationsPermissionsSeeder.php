<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Identity\Support\ApplicationPermission as P;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class OperationsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ([P::OPERATIONS_MANAGE_MEETINGS, P::OPERATIONS_VIEW_ALL_TASKS] as $name) {
            Permission::findOrCreate($name, P::GUARD);
        }

        $admin = Role::findByName(ApplicationRole::ADMIN, P::GUARD);
        $admin->givePermissionTo([P::OPERATIONS_MANAGE_MEETINGS, P::OPERATIONS_VIEW_ALL_TASKS]);

        $super = Role::findByName(ApplicationRole::SUPERADMIN, P::GUARD);
        $super->givePermissionTo([P::OPERATIONS_MANAGE_MEETINGS, P::OPERATIONS_VIEW_ALL_TASKS]);

        // Staff : plus de création de réunion par défaut (retirer meeting.create / delete si présents).
        $staff = Role::findByName(ApplicationRole::STAFF, P::GUARD);
        $staff->revokePermissionTo([
            P::name('meeting', P::CREATE),
            P::name('meeting', P::DELETE),
        ]);
    }
}
