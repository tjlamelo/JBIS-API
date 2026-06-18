<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word;

        return [
            'name' => ['fr' => $name, 'en' => $name],
            'slug' => Str::slug($name),
            'description' => [
                'fr' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
            'is_active' => true,
        ];
    }
}
