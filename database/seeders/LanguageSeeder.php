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
            ['code' => 'en', 'name' => ['fr' => 'Anglais', 'en' => 'English'], 'flag_icon' => '🇬🇧'],
            ['code' => 'fr', 'name' => ['fr' => 'Français', 'en' => 'French'], 'flag_icon' => '🇫🇷'],
            ['code' => 'ar', 'name' => ['fr' => 'Arabe', 'en' => 'Arabic'], 'flag_icon' => '🇸🇦'],
            ['code' => 'es', 'name' => ['fr' => 'Espagnol', 'en' => 'Spanish'], 'flag_icon' => '🇪🇸'],
            ['code' => 'de', 'name' => ['fr' => 'Allemand', 'en' => 'German'], 'flag_icon' => '🇩🇪'],
            ['code' => 'zh', 'name' => ['fr' => 'Chinois mandarin', 'en' => 'Mandarin Chinese'], 'flag_icon' => '🇨🇳'],
            ['code' => 'hi', 'name' => ['fr' => 'Hindî', 'en' => 'Hindi'], 'flag_icon' => '🇮🇳'],
            ['code' => 'bn', 'name' => ['fr' => 'Bengali', 'en' => 'Bengali'], 'flag_icon' => '🇧🇩'],
            ['code' => 'pt', 'name' => ['fr' => 'Portugais', 'en' => 'Portuguese'], 'flag_icon' => '🇵🇹'],
            ['code' => 'ru', 'name' => ['fr' => 'Russe', 'en' => 'Russian'], 'flag_icon' => '🇷🇺'],
            ['code' => 'ur', 'name' => ['fr' => 'Ourdou', 'en' => 'Urdu'], 'flag_icon' => '🇵🇰'],
            ['code' => 'id', 'name' => ['fr' => 'Indonésien', 'en' => 'Indonesian'], 'flag_icon' => '🇮🇩'],

        ];

        // Préparer les données pour l'upsert
        $data = array_map(fn ($lang) => [
            'code' => $lang['code'],
            'name' => json_encode($lang['name'], JSON_UNESCAPED_UNICODE),
            'flag_icon' => $lang['flag_icon'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $languages);

        // Une seule requête au lieu d'une boucle
        DB::table('languages')->upsert($data, ['code'], ['name', 'flag_icon', 'updated_at']);
    }
}
