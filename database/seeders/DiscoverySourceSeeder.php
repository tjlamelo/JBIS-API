<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Communication\Models\DiscoverySource;
use Illuminate\Database\Seeder;

class DiscoverySourceSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['key' => 'facebook', 'label' => 'Facebook', 'sort_order' => 10],
            ['key' => 'tiktok', 'label' => 'TikTok', 'sort_order' => 20],
            ['key' => 'instagram', 'label' => 'Instagram', 'sort_order' => 30],
            ['key' => 'linkedin', 'label' => 'LinkedIn', 'sort_order' => 40],
            ['key' => 'whatsapp', 'label' => 'WhatsApp', 'sort_order' => 50],
            ['key' => 'google_search', 'label' => 'Recherche Google', 'sort_order' => 60],
            ['key' => 'radio', 'label' => 'Radio', 'sort_order' => 70],
            ['key' => 'advertising', 'label' => 'Publicité', 'sort_order' => 80],
            ['key' => 'cameroon_desk', 'label' => 'Cameroon Desk', 'sort_order' => 90],
            ['key' => 'word_of_mouth', 'label' => 'Bouche à oreille', 'sort_order' => 100],
            ['key' => 'other', 'label' => 'Autre', 'sort_order' => 999],
        ];

        foreach ($items as $item) {
            DiscoverySource::query()->updateOrCreate(
                ['key' => $item['key']],
                [
                    'label' => $item['label'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
