<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationLevelSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
            ['slug' => 'none', 'name' => ['fr' => 'Sans diplôme', 'en' => 'No diploma']],
            ['slug' => 'cep', 'name' => ['fr' => 'CEP', 'en' => 'Primary Certificate']],
            ['slug' => 'bepc', 'name' => ['fr' => 'BEPC', 'en' => 'Middle School Certificate']],
            ['slug' => 'bac', 'name' => ['fr' => 'Baccalauréat', 'en' => 'High School Diploma']],
            ['slug' => 'bachelor', 'name' => ['fr' => 'Licence', 'en' => "Bachelor's Degree"]],
            ['slug' => 'master', 'name' => ['fr' => 'Master', 'en' => "Master's Degree"]],
            ['slug' => 'doctorate', 'name' => ['fr' => 'Doctorat', 'en' => 'Doctorate']],
        ];

        foreach ($items as $item) {
            DB::table('education_levels')->updateOrInsert(
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
