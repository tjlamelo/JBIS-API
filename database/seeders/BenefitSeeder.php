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
            ['name' => ['fr' => 'Logement fourni', 'en' => 'Housing provided']],
            ['name' => ['fr' => 'Nutrition (repas inclus)', 'en' => 'Nutrition (meals included)']],
            ['name' => ['fr' => 'Assurance maladie', 'en' => 'Health insurance']],
        ];

        foreach ($benefits as $benefit) {
            $slug = Str::slug($benefit['name']['en']);

            $model = Benefit::query()->firstOrNew(['slug' => $slug]);
            $model->setTranslations('name', $benefit['name']);
            $model->save();
        }
    }
}
