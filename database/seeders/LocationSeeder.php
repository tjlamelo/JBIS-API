<?php

namespace Database\Seeders;

use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Location\Models\Region;
use App\Core\Domain\Location\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. On s'assure d'avoir un pays (Ex: Canada)
        // Note: Si tu as un CountrySeeder, utilise-le. Sinon, créons-le ici.
        $canada = Country::firstOrCreate(
            ['code' => 'CA'],
            [
                'name' => ['fr' => 'Canada', 'en' => 'Canada'],
                'slug' => 'canada',
                'phone_code' => '1'
            ]
        );

        // 2. Création d'une Région (Ex: Ontario)
        $ontario = Region::updateOrCreate(
            ['slug' => 'ontario', 'country_id' => $canada->id],
            ['name' => ['fr' => 'Ontario', 'en' => 'Ontario']]
        );

        // 3. Création d'une Ville (Ex: Toronto)
        City::updateOrCreate(
            ['slug' => 'toronto', 'region_id' => $ontario->id],
            [
                'name' => ['fr' => 'Toronto', 'en' => 'Toronto'],
                'zip_code' => 'M5H 2N2'
            ]
        );

        // --- Ajout d'une option Moyen-Orient pour tes tests ---
        $uae = Country::firstOrCreate(
            ['code' => 'AE'],
            ['name' => ['fr' => 'Émirats Arabes Unis', 'en' => 'United Arab Emirates'], 'slug' => 'uae', 'phone_code' => '971']
        );

        $dubaiRegion = Region::updateOrCreate(
            ['slug' => 'dubai-region', 'country_id' => $uae->id],
            ['name' => ['fr' => 'Dubaï', 'en' => 'Dubai']]
        );

        City::updateOrCreate(
            ['slug' => 'dubai-city', 'region_id' => $dubaiRegion->id],
            ['name' => ['fr' => 'Dubaï Centre', 'en' => 'Dubai Downtown'], 'zip_code' => '00000']
        );
    }
}