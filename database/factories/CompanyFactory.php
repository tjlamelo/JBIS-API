<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\Company;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\States\CompanyStatus;
use App\Core\Domain\Catalog\States\CompanyType;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'category_id' => Category::query()->inRandomOrder()->value('id'),
            'country_id' => Country::query()->inRandomOrder()->value('id'),
            'city_id' => City::query()->inRandomOrder()->value('id'),
            'address' => $this->faker->streetAddress(),
            'type' => $this->faker->randomElement(array_column(CompanyType::cases(), 'value')),
            'status' => $this->faker->randomElement([CompanyStatus::Published->value, CompanyStatus::Draft->value]),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),
            'description' => $this->faker->paragraph(3),
            'logo' => null,
            'is_approved' => $this->faker->boolean(80),
        ];
    }
}
