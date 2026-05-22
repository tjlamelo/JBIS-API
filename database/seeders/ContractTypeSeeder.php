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
            ['fr' => 'CDI', 'en' => 'Full-time', 'color' => '#00ff88'],
            ['fr' => 'CDD', 'en' => 'Fixed-term', 'color' => '#00d1ff'],
            ['fr' => 'Freelance', 'en' => 'Freelance', 'color' => '#d4af37'],
            ['fr' => 'Stage', 'en' => 'Internship', 'color' => '#ff00ff'],
        ];

        foreach ($types as $type) {
            $slug = Str::slug($type['en']);

            $contractType = ContractType::query()->firstOrNew(['slug' => $slug]);
            $contractType->color_code = $type['color'];
            $contractType->setTranslations('name', [
                'fr' => $type['fr'],
                'en' => $type['en'],
            ]);
            $contractType->save();
        }
    }
}
