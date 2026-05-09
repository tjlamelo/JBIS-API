<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
            ['slug' => 'day', 'name' => ['fr' => 'Horaire de jour', 'en' => 'Day shift']],
            ['slug' => 'night', 'name' => ['fr' => 'Horaire de nuit', 'en' => 'Night shift']],
            ['slug' => 'rotating', 'name' => ['fr' => 'Horaire rotatif', 'en' => 'Rotating shifts']],
            ['slug' => 'flexible', 'name' => ['fr' => 'Horaire flexible', 'en' => 'Flexible hours']],
        ];

        foreach ($items as $item) {
            DB::table('work_schedules')->updateOrInsert(
                ['slug' => $item['slug']],
                [
                    'name' => json_encode($item['name'], JSON_UNESCAPED_UNICODE),
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
