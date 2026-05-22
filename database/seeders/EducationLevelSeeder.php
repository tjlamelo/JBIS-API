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
            ['slug' => 'cap', 'name' => ['fr' => 'CAP', 'en' => 'Vocational training certificate']],
            ['slug' => 'bac', 'name' => ['fr' => 'Baccalauréat', 'en' => 'High School Diploma']],
            ['slug' => 'bts', 'name' => ['fr' => 'BTS', 'en' => 'Higher technician certificate']],
            ['slug' => 'dut', 'name' => ['fr' => 'DUT', 'en' => 'University technical diploma']],
            ['slug' => 'deug', 'name' => ['fr' => 'DEUG', 'en' => 'General university studies diploma']],
            ['slug' => 'bachelor', 'name' => ['fr' => 'Licence', 'en' => "Bachelor's Degree"]],
            ['slug' => 'licence_pro', 'name' => ['fr' => 'Licence professionnelle', 'en' => 'Professional bachelor\'s degree']],
            ['slug' => 'master', 'name' => ['fr' => 'Master', 'en' => "Master's Degree"]],
            ['slug' => 'master_pro', 'name' => ['fr' => 'Master professionnel', 'en' => 'Professional master\'s degree']],
            ['slug' => 'master_rech', 'name' => ['fr' => 'Master recherche', 'en' => 'Research master\'s degree']],
            ['slug' => 'mba', 'name' => ['fr' => 'MBA', 'en' => 'MBA']],
            ['slug' => 'doctorate', 'name' => ['fr' => 'Doctorat', 'en' => 'Doctorate']],
            ['slug' => 'postdoc', 'name' => ['fr' => 'Post-doctorat', 'en' => 'Post-doctorate']],
            ['slug' => 'hdr', 'name' => ['fr' => 'HDR', 'en' => 'Habilitation to supervise research']],
        ];

        // Préparer les données pour l'upsert
        $data = array_map(fn ($item) => [
            'slug' => $item['slug'],
            'name' => json_encode($item['name'], JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ], $items);

        // Une seule requête au lieu de N
        DB::table('education_levels')->upsert($data, ['slug'], ['name', 'updated_at']);
    }
}
