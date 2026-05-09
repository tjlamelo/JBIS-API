<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\States\ProgramStatus;
use App\Core\Domain\Identity\Models\User;
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

        return [
            // --- CHAMPS TRADUISIBLES (JSON) ---
            'name' => [
                'fr' => "Programme " . $name,
                'en' => $name . " Program",
            ],
            'description' => [
                'fr' => $this->faker->paragraph(5),
                'en' => $this->faker->paragraph(5),
            ],
            'folder_composition' => [
                'fr' => "Passeport, diplômes, photos d'identité.",
                'en' => "Passport, diplomas, ID photos.",
            ],
          
            
            // --- SEO & META (JSON) ---
            'meta' => [
                'fr' => [
                    'seo_title' => "Comment immigrer via " . $name,
                    'seo_description' => "Découvrez les étapes et coûts pour le programme " . $name
                ],
                'en' => [
                    'seo_title' => "How to migrate via " . $name,
                    'seo_description' => "Discover steps and costs for " . $name
                ]
            ],

            // --- DONNÉES TECHNIQUES & FILTRES ---
            'geographic_zone' => $this->faker->randomElement(['Schengen', 'Afrique Centrale', 'Amérique du Nord']),
            'country' => $this->faker->country(),
            
            // --- COÛT ET DURÉE ---
            'procedure_cost' => $this->faker->randomFloat(2, 500, 5000),
            'currency' => $this->faker->randomElement(['XAF', 'EUR', 'CAD']),
            'procedure_duration' => $this->faker->numberBetween(3, 12),
            'duration_unit' => $this->faker->randomElement(['months', 'weeks']),

            'required_age' => $this->faker->numberBetween(18, 45) . ' ans',
            'language' => $this->faker->randomElement(['Français', 'Anglais', 'Allemand']),
            'image' => null, // À gérer via un seeder d'images ou Spatie Media Library

            // --- RELATIONS ---
            'user_id' => null, // Préférable de le définir dans le Seeder pour plus de contrôle

            // --- STATUT & DATES ---
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