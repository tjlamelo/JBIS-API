<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfferTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $items = [
            ['slug' => 'job', 'name' => ['fr' => 'Emploi', 'en' => 'Job']],
            ['slug' => 'internship', 'name' => ['fr' => 'Stage', 'en' => 'Internship']],
            ['slug' => 'freelance', 'name' => ['fr' => 'Freelance', 'en' => 'Freelance']],
            ['slug' => 'temporary', 'name' => ['fr' => 'Temporaire', 'en' => 'Temporary']],
        ];

        foreach ($items as $item) {
            DB::table('offer_types')->updateOrInsert(
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
