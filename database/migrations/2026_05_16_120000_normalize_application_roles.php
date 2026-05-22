<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        $pivotTable = config('permission.table_names.model_has_roles');
        $modelKey = config('permission.column_names.model_morph_key');

        $candidate = Role::query()->firstOrCreate([
            'name' => 'candidate',
            'guard_name' => $guard,
        ]);

        $userRole = Role::query()
            ->where('name', 'user')
            ->where('guard_name', $guard)
            ->first();

        if ($userRole) {
            $assignments = DB::table($pivotTable)->where('role_id', $userRole->id)->get();

            foreach ($assignments as $row) {
                $exists = DB::table($pivotTable)
                    ->where('role_id', $candidate->id)
                    ->where($modelKey, $row->{$modelKey})
                    ->where('model_type', $row->model_type)
                    ->exists();

                if (! $exists) {
                    DB::table($pivotTable)->insert([
                        'role_id' => $candidate->id,
                        'model_type' => $row->model_type,
                        $modelKey => $row->{$modelKey},
                    ]);
                }

                DB::table($pivotTable)
                    ->where('role_id', $userRole->id)
                    ->where($modelKey, $row->{$modelKey})
                    ->where('model_type', $row->model_type)
                    ->delete();
            }

            $userRole->delete();
        }

        foreach (['guest', 'custom_user'] as $obsolete) {
            $role = Role::query()
                ->where('name', $obsolete)
                ->where('guard_name', $guard)
                ->first();

            if (! $role) {
                continue;
            }

            DB::table($pivotTable)->where('role_id', $role->id)->delete();
            $role->delete();
        }
    }

    public function down(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        Role::query()->firstOrCreate(['name' => 'user', 'guard_name' => $guard]);
        Role::query()->firstOrCreate(['name' => 'guest', 'guard_name' => $guard]);
        Role::query()->firstOrCreate(['name' => 'custom_user', 'guard_name' => $guard]);

        $candidate = Role::query()
            ->where('name', 'candidate')
            ->where('guard_name', $guard)
            ->first();

        if ($candidate) {
            $candidate->delete();
        }
    }
};
