<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => ['fr' => 'Anglais', 'en' => 'English']],
            ['code' => 'fr', 'name' => ['fr' => 'Français', 'en' => 'French']],
            ['code' => 'ar', 'name' => ['fr' => 'Arabe', 'en' => 'Arabic']],
            ['code' => 'es', 'name' => ['fr' => 'Espagnol', 'en' => 'Spanish']],
            ['code' => 'de', 'name' => ['fr' => 'Allemand', 'en' => 'German']],
            ['code' => 'zh', 'name' => ['fr' => 'Chinois mandarin', 'en' => 'Mandarin Chinese']],
            ['code' => 'pt', 'name' => ['fr' => 'Portugais', 'en' => 'Portuguese']],
            ['code' => 'ru', 'name' => ['fr' => 'Russe', 'en' => 'Russian']],
        ];

        $data = array_map(static fn (array $lang) => [
            'code' => $lang['code'],
            'name' => json_encode($lang['name'], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ], $languages);

        DB::table('languages')->upsert($data, ['code'], ['name', 'updated_at']);
    }
}
