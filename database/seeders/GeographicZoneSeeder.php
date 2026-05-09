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
                'name' => ['fr' => 'Amérique du Nord', 'en' => 'North America'],
                'icon' => 'map', // Icône Lucide: Map
                'sort_order' => 1,
            ],
            [
                'name' => ['fr' => 'Europe (Espace Schengen)', 'en' => 'Europe (Schengen Area)'],
                'icon' => 'globe', // Icône Lucide: Globe
                'sort_order' => 2,
            ],
            [
                'name' => ['fr' => 'Europe (Hors Schengen)', 'en' => 'Europe (Non-Schengen)'],
                'icon' => 'navigation', // Icône Lucide: Navigation
                'sort_order' => 3,
            ],
            [
                'name' => ['fr' => 'Moyen-Orient', 'en' => 'Middle East'],
                'icon' => 'landmark', // Icône Lucide: Landmark
                'sort_order' => 4,
            ],
            [
                'name' => ['fr' => 'Asie & Pacifique', 'en' => 'Asia & Pacific'],
                'icon' => 'compass', // Icône Lucide: Compass
                'sort_order' => 5,
            ],
        ];

        foreach ($zones as $zone) {
            GeographicZone::create([
                'name' => $zone['name'],
                'slug' => Str::slug($zone['name']['en']),
                'icon' => $zone['icon'],
                'sort_order' => $zone['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}