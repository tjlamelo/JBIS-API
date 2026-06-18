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
            ['fr' => 'CDI', 'en' => 'Full-time'],
            ['fr' => 'CDD', 'en' => 'Fixed-term'],
            ['fr' => 'Freelance', 'en' => 'Freelance'],
            ['fr' => 'Stage', 'en' => 'Internship'],
        ];

        foreach ($types as $type) {
            $slug = Str::slug($type['en']);

            $contractType = ContractType::query()->firstOrNew(['slug' => $slug]);
            $contractType->setTranslations('name', [
                'fr' => $type['fr'],
                'en' => $type['en'],
            ]);
            $contractType->save();
        }
    }
}
