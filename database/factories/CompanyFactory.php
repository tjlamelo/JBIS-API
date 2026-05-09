<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        
        return [
            // Infos principales
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(5), // <--- Génération explicite
            'industry' => $this->faker->randomElement(['Tech', 'Finance', 'Education', 'Health', 'Manufacturing']),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'address' => $this->faker->streetAddress(),

            // Contact
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),

            // Status
            'is_approved' => $this->faker->boolean(80), // 80% de chance d'être approuvé
            
            // Description
            'description' => $this->faker->paragraph(3),
            'logo' => null,

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}