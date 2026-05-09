<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\SkillCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            'technique' => ['fr' => 'Technique', 'en' => 'Technical'],
            'langue' => ['fr' => 'Langue', 'en' => 'Language'],
            'soft-skill' => ['fr' => 'Compétence comportementale', 'en' => 'Soft skill'],
        ];

        foreach ($categories as $slug => $name) {
            SkillCategory::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                ]
            );
        }

        $categoryIds = SkillCategory::query()
            ->whereIn('slug', array_keys($categories))
            ->get(['id', 'slug'])
            ->pluck('id', 'slug');

        $skills = [
            // Technique
            ['fr' => 'Laravel', 'en' => 'Laravel', 'category' => 'technique'],
            ['fr' => 'PHP', 'en' => 'PHP', 'category' => 'technique'],
            ['fr' => 'MySQL', 'en' => 'MySQL', 'category' => 'technique'],
            ['fr' => 'JavaScript', 'en' => 'JavaScript', 'category' => 'technique'],
            ['fr' => 'React', 'en' => 'React', 'category' => 'technique'],
            ['fr' => 'Conduite défensive', 'en' => 'Defensive driving', 'category' => 'technique'],
            ['fr' => 'Maintenance de base véhicule', 'en' => 'Basic vehicle maintenance', 'category' => 'technique'],

            // Langues
            ['fr' => 'Français professionnel', 'en' => 'Professional French', 'category' => 'langue'],
            ['fr' => 'Anglais professionnel', 'en' => 'Professional English', 'category' => 'langue'],

            // Soft skills
            ['fr' => 'Communication', 'en' => 'Communication', 'category' => 'soft-skill'],
            ['fr' => 'Travail en équipe', 'en' => 'Teamwork', 'category' => 'soft-skill'],
            ['fr' => 'Gestion du stress', 'en' => 'Stress management', 'category' => 'soft-skill'],
            ['fr' => 'Service client', 'en' => 'Customer service', 'category' => 'soft-skill'],
            ['fr' => 'Ponctualité', 'en' => 'Punctuality', 'category' => 'soft-skill'],
        ];

        foreach ($skills as $skill) {
            $slug = Str::slug($skill['en']);

            DB::table('skills')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => json_encode(
                        ['fr' => $skill['fr'], 'en' => $skill['en']],
                        JSON_UNESCAPED_UNICODE
                    ),
                    'skill_category_id' => $categoryIds[$skill['category']] ?? null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
