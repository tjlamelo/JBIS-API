<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Identity\Support\ApplicationPermission as P;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Allow running RoleSeeder alone without missing permission errors.
        $this->call(PermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

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

                ApplicationRole::PARTNER => $this->partnerPermissions(),

                ApplicationRole::CANDIDATE => $all->whereIn('name', [
                    P::name('application', P::VIEW),
                    P::name('application', P::CREATE),
                    P::name('offer', P::VIEW),
                ])->pluck('name')->all(),

                ApplicationRole::RECRUITER => $this->recruiterPermissions(),

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
            'userpreferredcountry', 'uservisahistory', 'usernote', 'archive',
            'meeting', 'assignedtask', 'dailytask', 'usersecurityevent',
            'application', 'offer', 'company', 'program', 'training', 'certificationoffer', 'processflow', 'processstep',
            'recruiterorganization', 'recruiteronboarding', 'recruiteroffer', 'recruiterassignment', 'recruiterprofilerequest',
            'partnerorganization', 'partnercohort', 'partnercohortstudent',
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
            P::name('certificationoffer', P::VIEW),
            P::name('certificationoffer', P::CREATE),
            P::name('certificationoffer', P::UPDATE),
            P::name('userinternship', P::VIEW),
            P::name('userinternship', P::CREATE),
            P::name('userinternship', P::UPDATE),
            P::name('interestandhobby', P::VIEW),
            P::name('usernote', P::VIEW),
            P::name('usernote', P::CREATE),
            P::name('usernote', P::UPDATE),
            P::name('archive', P::VIEW),
            P::name('archive', P::CREATE),
            P::name('archive', P::UPDATE),
            P::name('archive', P::DELETE),
            ...$readUpdate('meeting'),
            P::name('meeting', P::DELETE),
            ...$readUpdate('assignedtask'),
            P::name('assignedtask', P::DELETE),
            ...$readUpdate('dailytask'),
            P::name('dailytask', P::DELETE),
            P::name('usersecurityevent', P::VIEW),
            P::name('user', P::VIEW),
            P::name('recruiterorganization', P::VIEW),
            P::name('recruiteronboarding', P::VIEW),
            P::name('recruiteronboarding', P::UPDATE),
            P::name('recruiteroffer', P::VIEW),
            P::name('recruiteroffer', P::UPDATE),
            P::name('recruiterassignment', P::VIEW),
            P::name('recruiterassignment', P::CREATE),
            P::name('recruiterassignment', P::UPDATE),
            P::name('recruiterprofilerequest', P::VIEW),
            P::name('recruiterprofilerequest', P::UPDATE),
            P::name('partnerorganization', P::VIEW),
            P::name('partnercohort', P::VIEW),
            P::name('partnercohort', P::UPDATE),
            P::name('partnercohortstudent', P::VIEW),
            P::name('partnercohortstudent', P::UPDATE),
        ];
    }

    /**
     * @return list<string>
     */
    private function partnerPermissions(): array
    {
        return [
            P::name('partnerorganization', P::VIEW),
            P::name('partnercohort', P::VIEW),
            P::name('partnercohort', P::CREATE),
            P::name('partnercohort', P::UPDATE),
            P::name('partnercohortstudent', P::VIEW),
            P::name('partnercohortstudent', P::CREATE),
            P::name('partnercohortstudent', P::UPDATE),
            P::name('userdocument', P::VIEW),
        ];
    }

    /**
     * @return list<string>
     */
    private function recruiterPermissions(): array
    {
        return [
            P::name('recruiterorganization', P::VIEW),
            P::name('recruiteroffer', P::VIEW),
            P::name('recruiteroffer', P::CREATE),
            P::name('recruiteroffer', P::UPDATE),
            P::name('recruiterprofilerequest', P::VIEW),
            P::name('recruiterprofilerequest', P::CREATE),
            P::name('recruiterprofilerequest', P::UPDATE),
            P::name('recruiterassignment', P::VIEW),
            P::name('userprofile', P::VIEW),
            P::name('userdocument', P::VIEW),
        ];
    }
}
