<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Agency;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@jbis.cm')->first();
        $cameroon = Country::query()->where('code', 'CM')->first();

        $items = [
            [
                'name_fr' => 'Agence Yaoundé',
                'name_en' => 'Yaounde Agency',
                'city_slug' => 'yaounde',
                'address' => 'Rond point Damas, près de la maison bleue',
                'email' => 'agence.yaounde@jbis.cm',
                'phones' => ['+237686532128', '+237671260045'],
                'description_fr' => 'Agence située à Yaoundé.',
                'description_en' => 'Agency located in Yaounde.',
            ],
            [
                'name_fr' => 'Agence Douala',
                'name_en' => 'Douala Agency',
                'city_slug' => 'douala',
                'address' => 'Akwa, immeuble ancienne Sonel, porte 302',
                'email' => 'agence.douala@jbis.cm',
                'phones' => ['+237689924407', '+237678386420'],
                'description_fr' => 'Agence située à Douala.',
                'description_en' => 'Agency located in Douala.',
            ],
        ];

        foreach ($items as $item) {
            $city = City::query()->where('slug', $item['city_slug'])->first();
            $slugBase = Str::slug($item['name_fr']);

            Agency::query()->updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => ['fr' => $item['name_fr'], 'en' => $item['name_en']],
                    'slug' => $slugBase,
                    'description' => ['fr' => $item['description_fr'], 'en' => $item['description_en']],
                    'country_id' => $cameroon?->id,
                    'city_id' => $city?->id,
                    'address' => $item['address'],
                    'phones' => $item['phones'],
                    'whatsapp_numbers' => $item['phones'],
                    'manager_id' => $admin?->id,
                    'number_of_employees' => 0,
                    'opening_hours' => null,
                    'image_url' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
