<?php

namespace Database\Seeders;

use App\Core\Domain\Location\Models\GeographicZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GeographicZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => ['fr' => 'Afrique', 'en' => 'Africa'],
                'sort_order' => 1,
            ],
            [
                'name' => ['fr' => 'Amérique du Nord', 'en' => 'North America'],
                'sort_order' => 2,
            ],
            [
                'name' => ['fr' => 'Amérique latine & Caraïbes', 'en' => 'Latin America & Caribbean'],
                'sort_order' => 3,
            ],
            [
                'name' => ['fr' => 'Europe (Espace Schengen)', 'en' => 'Europe (Schengen Area)'],
                'sort_order' => 4,
            ],
            [
                'name' => ['fr' => 'Europe (Hors Schengen)', 'en' => 'Europe (Non-Schengen)'],
                'sort_order' => 5,
            ],
            [
                'name' => ['fr' => 'Moyen-Orient', 'en' => 'Middle East'],
                'sort_order' => 6,
            ],
            [
                'name' => ['fr' => 'Asie du Sud-Est', 'en' => 'Southeast Asia'],
                'sort_order' => 7,
            ],
            [
                'name' => ['fr' => 'Asie & Pacifique', 'en' => 'Asia & Pacific'],
                'sort_order' => 8,
            ],
            [
                'name' => ['fr' => 'Océanie', 'en' => 'Oceania'],
                'sort_order' => 9,
            ],
        ];

        $data = array_map(function ($zone) {
            return [
                'slug' => Str::slug($zone['name']['en']),
                'name' => json_encode($zone['name'], JSON_UNESCAPED_UNICODE),
                'sort_order' => $zone['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $zones);

        GeographicZone::flushEventListeners();

        GeographicZone::upsert($data, ['slug'], ['name', 'sort_order', 'is_active', 'updated_at']);
    }
}
