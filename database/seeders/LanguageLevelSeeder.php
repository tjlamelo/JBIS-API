<?php

namespace Database\Seeders;

use App\Core\Domain\Location\Models\LanguageLevel;
use Illuminate\Database\Seeder;

class LanguageLevelSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'elementary_proficiency', 'name' => ['fr' => 'Competence elementaire', 'en' => 'Elementary proficiency'], 'sort_order' => 10],
            ['code' => 'limited_working_proficiency', 'name' => ['fr' => 'Competence professionnelle limitee', 'en' => 'Limited working proficiency'], 'sort_order' => 20],
            ['code' => 'professional_working_proficiency', 'name' => ['fr' => 'Competence professionnelle', 'en' => 'Professional working proficiency'], 'sort_order' => 30],
            ['code' => 'full_professional_proficiency', 'name' => ['fr' => 'Pleine competence professionnelle', 'en' => 'Full professional proficiency'], 'sort_order' => 40],
            ['code' => 'native_or_bilingual_proficiency', 'name' => ['fr' => 'Langue maternelle ou bilingue', 'en' => 'Native or bilingual proficiency'], 'sort_order' => 50],
        ];

        foreach ($rows as $row) {
            LanguageLevel::query()->updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name'], 'sort_order' => $row['sort_order'], 'is_active' => true]
            );
        }
    }
}
