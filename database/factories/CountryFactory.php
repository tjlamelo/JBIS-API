<?php

namespace Database\Factories;

use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class; // <-- TRÈS IMPORTANT

    public function definition(): array
    {
        return [
            'name' => ['fr' => $this->faker->country, 'en' => $this->faker->country],
            'code' => $this->faker->unique()->countryCode,
            'is_active' => true,
        ];
    }
}