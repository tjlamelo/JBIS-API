<?php

namespace Database\Seeders;

use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => ['fr' => 'Cameroun', 'en' => 'Cameroon'], 'code' => 'CM', 'phone_code' => '+237', 'flag' => '🇨🇲'],
            ['name' => ['fr' => 'Canada', 'en' => 'Canada'], 'code' => 'CA', 'phone_code' => '+1', 'flag' => '🇨🇦'],
            ['name' => ['fr' => 'Belgique', 'en' => 'Belgium'], 'code' => 'BE', 'phone_code' => '+32', 'flag' => '🇧🇪'],
            ['name' => ['fr' => 'Allemagne', 'en' => 'Germany'], 'code' => 'DE', 'phone_code' => '+49', 'flag' => '🇩🇪'],
            ['name' => ['fr' => 'Albanie', 'en' => 'Albania'], 'code' => 'AL', 'phone_code' => '+355', 'flag' => '🇦🇱'],
            ['name' => ['fr' => 'Émirats Arabes Unis', 'en' => 'United Arab Emirates'], 'code' => 'AE', 'phone_code' => '+971', 'flag' => '🇦🇪'],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(['code' => $country['code']], $country + ['is_active' => true]);
        }
    }
}