<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Core\Domain\Catalog\Models\Agency;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agencies = [
            [
                'name' => 'Agence Yaoundé',
                'country' => 'Cameroun',
                'city' => 'Yaoundé',
                'address' => 'Rond point Damas, près de la maison bleue',
                'email' => 'agence.yaounde@jbis.cm',
                'phone' => '+237 686532128 / +237 671260045',
                'manager_name' => 'null',
                'description' => 'Agence située à Yaoundé.',
                'number_of_employees' => null,
            ],
            [
                'name' => 'Agence Douala',
                'country' => 'Cameroun',
                'city' => 'Douala',
                'address' => 'Akwa, immeuble ancienne Sonel, porte 302',
                'email' => 'agence.douala@jbis.cm',
                'phone' => '+237 689924407 / +237 678386420',
                'manager_name' => null,
                'description' => 'Agence située à Douala.',
                'number_of_employees' => null,
            ],
            [
                'name' => 'Agence Buea',
                'country' => 'Cameroun',
                'city' => 'Buea',
                'address' => "Immeuble Book's n'Things, face université, 4ème étage porte 403",
                'email' => 'agence.douala@jbis.cm',
                'phone' => '+237 671808580',
                'manager_name' => null,
                'description' => 'Agence située à Buea.',
                'number_of_employees' => null,
            ],
        ];

        foreach ($agencies as $agency) {
            Agency::create($agency);
        }
    }
}
