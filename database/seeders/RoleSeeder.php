<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Identity\Support\ApplicationPermission as P;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $all = Permission::query()
            ->where('guard_name', P::GUARD)
            ->get();

        foreach (ApplicationRole::ALL as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => P::GUARD,
            ]);

            $names = match ($roleName) {
                ApplicationRole::SUPERADMIN => $all->pluck('name')->all(),

                ApplicationRole::ADMIN => $this->adminPermissions(),

                ApplicationRole::STAFF => $this->staffPermissions(),

                ApplicationRole::PARTNER => $all->whereIn('name', [
                    P::name('application', P::VIEW),
                    P::name('application', P::CREATE),
                    P::name('offer', P::VIEW),
                    P::name('company', P::VIEW),
                ])->pluck('name')->all(),

                ApplicationRole::CANDIDATE => $all->whereIn('name', [
                    P::name('application', P::VIEW),
                    P::name('application', P::CREATE),
                    P::name('offer', P::VIEW),
                ])->pluck('name')->all(),

                default => [],
            };

            $role->syncPermissions($names);
        }

        $pivotTable = config('permission.table_names.model_has_roles');

        foreach (['user', 'guest', 'custom_user'] as $obsolete) {
            $role = Role::where('name', $obsolete)->first();
            if ($role) {
                $role->permissions()->detach();
                DB::table($pivotTable)->where('role_id', $role->id)->delete();
                $role->delete();
            }
        }
    }

    /**
     * @return list<string>
     */
    private function adminPermissions(): array
    {
        $resources = [
            'user', 'userprofile', 'userdocument', 'userdevice', 'usersettings', 'userconsent',
            'experience', 'education', 'certification', 'userlanguage', 'userskill',
            'usertraining', 'userinternship', 'interestandhobby',
            'userpreferredcountry', 'uservisahistory', 'usernote',
            'application', 'offer', 'company', 'program', 'training', 'processflow', 'processstep',
        ];

        $names = [P::ADMIN_ACCESS, P::PERMISSION_MANAGE];
        foreach ($resources as $resource) {
            array_push($names, ...P::forResource($resource));
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    private function staffPermissions(): array
    {
        $readUpdate = static fn (string $r): array => [
            P::name($r, P::VIEW),
            P::name($r, P::CREATE),
            P::name($r, P::UPDATE),
        ];

        return [
            P::ADMIN_ACCESS,
            ...$readUpdate('application'),
            ...$readUpdate('offer'),
            P::name('company', P::VIEW),
            ...$readUpdate('userdocument'),
            ...$readUpdate('experience'),
            ...$readUpdate('education'),
            ...$readUpdate('certification'),
            ...$readUpdate('userlanguage'),
            P::name('userskill', P::VIEW),
            P::name('userskill', P::CREATE),
            P::name('userskill', P::UPDATE),
            P::name('usertraining', P::VIEW),
            P::name('training', P::VIEW),
            P::name('userinternship', P::VIEW),
            P::name('userinternship', P::CREATE),
            P::name('userinternship', P::UPDATE),
            P::name('interestandhobby', P::VIEW),
            P::name('usernote', P::VIEW),
            P::name('usernote', P::CREATE),
            P::name('usernote', P::UPDATE),
            P::name('user', P::VIEW),
        ];
    }
}
