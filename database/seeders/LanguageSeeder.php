<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $languages = [
            ['code' => 'fr', 'name' => ['fr' => 'Français', 'en' => 'French'], 'flag_icon' => '🇫🇷'],
            ['code' => 'en', 'name' => ['fr' => 'Anglais', 'en' => 'English'], 'flag_icon' => '🇬🇧'],
            ['code' => 'es', 'name' => ['fr' => 'Espagnol', 'en' => 'Spanish'], 'flag_icon' => '🇪🇸'],
            ['code' => 'de', 'name' => ['fr' => 'Allemand', 'en' => 'German'], 'flag_icon' => '🇩🇪'],
            ['code' => 'it', 'name' => ['fr' => 'Italien', 'en' => 'Italian'], 'flag_icon' => '🇮🇹'],
            ['code' => 'ar', 'name' => ['fr' => 'Arabe', 'en' => 'Arabic'], 'flag_icon' => '🇸🇦'],
        ];

        foreach ($languages as $language) {
            DB::table('languages')->updateOrInsert(
                ['code' => $language['code']],
                [
                    'name' => json_encode($language['name'], JSON_UNESCAPED_UNICODE),
                    'flag_icon' => $language['flag_icon'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
