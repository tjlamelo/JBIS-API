<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\States\ProgramStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        $name = $this->faker->sentence(3);
        $slugBase = $this->faker->slug();

        return [
            'name' => [
                'fr' => 'Programme '.$name,
                'en' => $name.' Program',
            ],
            'description' => [
                'fr' => $this->faker->paragraph(5),
                'en' => $this->faker->paragraph(5),
            ],
            'slug' => [
                'fr' => $slugBase.'-'.$this->faker->lexify('?????'),
                'en' => $slugBase.'-en-'.$this->faker->lexify('?????'),
            ],
            'geographic_zone_id' => null,
            'user_id' => null,
            'procedure_duration' => $this->faker->numberBetween(3, 12),
            'duration_unit' => $this->faker->randomElement(['months', 'weeks']),
            'age_min' => 18,
            'age_max' => 45,
            'is_featured' => $this->faker->boolean(20),
            'is_urgent' => $this->faker->boolean(10),
            'views_count' => $this->faker->numberBetween(0, 5000),
            'image_media' => null,
            'status' => $this->faker->randomElement([
                ProgramStatus::Draft->value,
                ProgramStatus::Published->value,
            ]),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+6 months', '+1 year'),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
