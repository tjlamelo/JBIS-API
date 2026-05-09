<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\OfferCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OfferCategoryFactory extends Factory
{
    protected $model = OfferCategory::class; // <-- TRÈS IMPORTANT

    public function definition(): array
    {
        $name = $this->faker->unique()->word;
        return [
            'name' => ['fr' => $name, 'en' => $name],
            'slug' => Str::slug($name),
            'is_active' => true,
        ];
    }
    protected static function newFactory()
{
    return \Database\Factories\OfferCategoryFactory::new();
}
}