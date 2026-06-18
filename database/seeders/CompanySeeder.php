<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Company;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\States\CompanyStatus;
use App\Core\Domain\Catalog\States\CompanyType;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $transport = Category::query()->where('slug', 'transport-mobility')->first();
        $health = Category::query()->where('slug', 'healthcare-medical')->first();
        $it = Category::query()->where('slug', 'it-technology')->first();
        $construction = Category::query()->where('slug', 'construction-civil-engineering')->first();

        $uae = Country::query()->where('code', 'AE')->first();
        $canada = Country::query()->where('code', 'CA')->first();
        $albania = Country::query()->where('code', 'AL')->first();

        $dubai = $uae
            ? City::query()->whereHas('region', fn ($q) => $q->where('country_id', $uae->id))->orderBy('id')->value('id')
            : null;
        $montreal = $canada
            ? City::query()->whereHas('region', fn ($q) => $q->where('country_id', $canada->id))->orderBy('id')->value('id')
            : null;
        $tirana = $albania
            ? City::query()->whereHas('region', fn ($q) => $q->where('country_id', $albania->id))->orderBy('id')->value('id')
            : null;

        $companies = [
            [
                'name' => 'Aman Taxi Dubai',
                'logo' => 'https://res.cloudinary.com/votre-cloud/taxi-dubai.png',
                'type' => CompanyType::Partner->value,
                'category_id' => $transport?->id,
                'country_id' => $uae?->id,
                'city_id' => $dubai,
            ],
            [
                'name' => 'Dubai Taxi Corporation (DTC)',
                'logo' => 'https://res.cloudinary.com/votre-cloud/dtc.png',
                'type' => CompanyType::Employer->value,
                'category_id' => $transport?->id,
                'country_id' => $uae?->id,
                'city_id' => $dubai,
            ],
            [
                'name' => 'Emirates Logistics',
                'logo' => 'https://res.cloudinary.com/votre-cloud/emirates-log.png',
                'type' => CompanyType::Partner->value,
                'category_id' => $transport?->id,
                'country_id' => $uae?->id,
                'city_id' => $dubai,
            ],
            [
                'name' => 'Santé Québec Services',
                'logo' => 'https://res.cloudinary.com/votre-cloud/quebec-health.png',
                'type' => CompanyType::Employer->value,
                'category_id' => $health?->id,
                'country_id' => $canada?->id,
                'city_id' => $montreal,
            ],
            [
                'name' => 'Construction Montréal Inc.',
                'logo' => 'https://res.cloudinary.com/votre-cloud/montreal-const.png',
                'type' => CompanyType::Employer->value,
                'category_id' => $construction?->id,
                'country_id' => $canada?->id,
                'city_id' => $montreal,
            ],
            [
                'name' => 'Tirana Tech Solutions',
                'logo' => 'https://res.cloudinary.com/votre-cloud/tirana-tech.png',
                'type' => CompanyType::Employer->value,
                'category_id' => $it?->id,
                'country_id' => $albania?->id,
                'city_id' => $tirana,
            ],
        ];

        foreach ($companies as $row) {
            Company::updateOrCreate(
                ['name' => $row['name']],
                [
                    ...$row,
                    'slug' => Str::slug($row['name']),
                    'status' => CompanyStatus::Published->value,
                    'is_approved' => true,
                ],
            );
        }
    }
}
