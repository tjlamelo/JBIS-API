<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Benefit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BenefitSeeder extends Seeder
{
    public function run(): void
    {
        $benefits = [
            ['name' => ['fr' => 'Logement fourni', 'en' => 'Housing provided'], 'icon' => 'home'],
            ['name' => ['fr' => 'Nutrition (repas inclus)', 'en' => 'Nutrition (meals included)'], 'icon' => 'apple'],
            ['name' => ['fr' => 'Assurance maladie', 'en' => 'Health insurance'], 'icon' => 'shield-check'],
        ];

        foreach ($benefits as $benefit) {
            $slug = Str::slug($benefit['name']['en']);

            $model = Benefit::query()->firstOrNew(['slug' => $slug]);
            $model->icon = $benefit['icon'];
            $model->setTranslations('name', $benefit['name']);
            $model->save();
        }
    }
}
