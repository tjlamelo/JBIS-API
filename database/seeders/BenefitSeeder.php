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
            ['name' => ['fr' => 'Billet d\'avion annuel', 'en' => 'Annual flight ticket'], 'icon' => 'plane'],
            ['name' => ['fr' => 'Assurance Maladie', 'en' => 'Health Insurance'], 'icon' => 'shield-check'],
            ['name' => ['fr' => 'Transport inclus', 'en' => 'Transport included'], 'icon' => 'bus'],
            ['name' => ['fr' => 'Télétravail possible', 'en' => 'Remote friendly'], 'icon' => 'laptop'],
        ];

        foreach ($benefits as $benefit) {
            Benefit::create([
                'name' => $benefit['name'],
                'slug' => Str::slug($benefit['name']['en']),
                'icon' => $benefit['icon'],
            ]);
        }
    }
}