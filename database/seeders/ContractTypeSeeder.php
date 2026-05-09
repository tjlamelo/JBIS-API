<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\ContractType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContractTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['fr' => 'CDI', 'en' => 'Full-time', 'color' => '#00ff88'], // Vert néon
            ['fr' => 'CDD', 'en' => 'Fixed-term', 'color' => '#00d1ff'], // Bleu néon
            ['fr' => 'Freelance', 'en' => 'Freelance', 'color' => '#d4af37'], // Doré JBIS
            ['fr' => 'Stage', 'en' => 'Internship', 'color' => '#ff00ff'], // Magenta néon
        ];

   foreach ($types as $type) {
    ContractType::create([
        'name' => ['fr' => $type['fr'], 'en' => $type['en']],
        // On passe un tableau pour le slug aussi
        'slug' => [
            'fr' => Str::slug($type['fr']), 
            'en' => Str::slug($type['en'])
        ],
        'color_code' => $type['color'],
    ]);
}
    }
}