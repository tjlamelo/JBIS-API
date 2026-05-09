<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\Experience;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $endDate = $this->faker->boolean(70) ? $this->faker->dateTimeBetween($startDate, 'now') : null;

        return [
            'user_id' => User::factory(), 
            'offer_title' => $this->faker->jobTitle(),
            'company_name' => $this->faker->company(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'responsibilities' => $this->faker->paragraph(),
            'achievements' => $this->faker->paragraph(),
        ];
    }
}
