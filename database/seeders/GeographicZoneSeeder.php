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
                'icon' => 'globe-africa',
                'sort_order' => 1,
            ],
            [
                'name' => ['fr' => 'Amérique du Nord', 'en' => 'North America'],
                'icon' => 'map',
                'sort_order' => 2,
            ],
            [
                'name' => ['fr' => 'Amérique latine & Caraïbes', 'en' => 'Latin America & Caribbean'],
                'icon' => 'globe-americas',
                'sort_order' => 3,
            ],
            [
                'name' => ['fr' => 'Europe (Espace Schengen)', 'en' => 'Europe (Schengen Area)'],
                'icon' => 'globe',
                'sort_order' => 4,
            ],
            [
                'name' => ['fr' => 'Europe (Hors Schengen)', 'en' => 'Europe (Non-Schengen)'],
                'icon' => 'navigation',
                'sort_order' => 5,
            ],
            [
                'name' => ['fr' => 'Moyen-Orient', 'en' => 'Middle East'],
                'icon' => 'landmark',
                'sort_order' => 6,
            ],
            [
                'name' => ['fr' => 'Asie du Sud-Est', 'en' => 'Southeast Asia'],
                'icon' => 'map-pin',
                'sort_order' => 7,
            ],
            [
                'name' => ['fr' => 'Asie & Pacifique', 'en' => 'Asia & Pacific'],
                'icon' => 'compass',
                'sort_order' => 8,
            ],
            [
                'name' => ['fr' => 'Océanie', 'en' => 'Oceania'],
                'icon' => 'island',
                'sort_order' => 9,
            ],
        ];

        // Préparer les données avec slug généré
        $data = array_map(function ($zone) {
            return [
                'slug' => Str::slug($zone['name']['en']),
                'name' => json_encode($zone['name'], JSON_UNESCAPED_UNICODE),
                'icon' => $zone['icon'],
                'sort_order' => $zone['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $zones);

        // Désactiver les événements pour accélérer
        GeographicZone::flushEventListeners();

        // Upsert en une seule requête
        GeographicZone::upsert($data, ['slug'], ['name', 'icon', 'sort_order', 'is_active', 'updated_at']);
    }
}
