<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Identity\Support\ApplicationPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ApplicationPermission::allNames() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => ApplicationPermission::GUARD,
            ]);
        }
    }
}
