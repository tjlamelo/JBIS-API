<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            // Dubaï - Transport & Logistique
            ['name' => 'Aman Taxi Dubai', 'logo' => 'https://res.cloudinary.com/votre-cloud/taxi-dubai.png'],
            ['name' => 'Dubai Taxi Corporation (DTC)', 'logo' => 'https://res.cloudinary.com/votre-cloud/dtc.png'],
            ['name' => 'Emirates Logistics', 'logo' => 'https://res.cloudinary.com/votre-cloud/emirates-log.png'],
            // Canada - Santé & BTP
            ['name' => 'Santé Québec Services', 'logo' => 'https://res.cloudinary.com/votre-cloud/quebec-health.png'],
            ['name' => 'Construction Montréal Inc.', 'logo' => 'https://res.cloudinary.com/votre-cloud/montreal-const.png'],
            // Albanie
            ['name' => 'Tirana Tech Solutions', 'logo' => 'https://res.cloudinary.com/votre-cloud/tirana-tech.png'],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(['name' => $company['name']], $company);
        }
    }
}