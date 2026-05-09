<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\OfferCategory;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        $salaryMin = $this->faker->numberBetween(1500, 3000);
        $salaryMax = $salaryMin + $this->faker->numberBetween(500, 2000);
        
        $titleFr = $this->faker->jobTitle() . ' (H/F)';
        $titleEn = $this->faker->jobTitle(); // On le stocke pour pouvoir l'utiliser dans le slug EN
        
        $location = $this->faker->city();
        $uniqueSuffix = Str::random(5);

        return [
            // --- CHAMPS TRADUISIBLES (JSON) ---
            'title' => [
                'fr' => $titleFr,
                'en' => $titleEn,
            ],
            'description' => [
                'fr' => "Description en français : " . $this->faker->paragraph(4),
                'en' => "English description: " . $this->faker->paragraph(4),
            ],
            'contract_type' => [
                'fr' => $this->faker->randomElement(['CDI', 'CDD', 'Stage']),
                'en' => $this->faker->randomElement(['Full-time', 'Contract', 'Internship']),
            ],
            'benefits' => [
                'fr' => "Assurance santé, transport gratuit.",
                'en' => "Health insurance, free transport.",
            ],
            'specific_documents' => [
                'fr' => "CV et Lettre de motivation.",
                'en' => "CV and Cover letter.",
            ],
            'responsibilities' => [
                'fr' => $this->faker->paragraph(3),
                'en' => $this->faker->paragraph(3),
            ],
            'requirements' => [
                'fr' => $this->faker->paragraph(2),
                'en' => $this->faker->paragraph(2),
            ],

            // --- LOCALISATION & SEO ---
            // Le slug est désormais bilingue (JSON)
            'slug' => [
                'fr' => Str::slug($titleFr . '-' . $location . '-' . $uniqueSuffix),
                'en' => Str::slug($titleEn . '-' . $location . '-' . $uniqueSuffix),
            ],
            'location' => $location,
            'region' => $this->faker->state(),
            'address' => $this->faker->address(),
            
            // --- RELATIONS ---
            'country_id' => Country::inRandomOrder()->first()?->id ?? Country::factory(),
            'offer_category_id' => OfferCategory::inRandomOrder()->first()?->id ?? OfferCategory::factory(),
            
            // --- SALAIRES, DEVISE & CONFIDENTIALITÉ ---
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'currency' => $this->faker->randomElement(['EUR', 'CAD', 'USD', 'XAF']),
            'is_salary_public' => $this->faker->boolean(80), // 80% de chances d'être public
            'is_company_public' => $this->faker->boolean(85), // 85% de chances d'être public

            // --- DÉTAILS ---
            'available_positions' => $this->faker->numberBetween(1, 5),
            'language' => $this->faker->randomElement(['FR', 'EN', 'FR/EN']),
            
            // --- NOUVELLE STRUCTURE META (SEO & Options) ---
            'meta' => [
                'is_featured' => $this->faker->boolean(15), // 15% d'offres "Vedette"
                'is_urgent' => $this->faker->boolean(10),   // 10% d'offres "Urgentes"
                'seo' => [
                    'title' => "Recrutement : " . $titleFr . " à " . $location,
                    'description' => "Postulez dès maintenant pour ce poste sur la plateforme JBIS. Opportunité de carrière.",
                    'robots' => 'index, follow'
                ],
                'external_link' => $this->faker->boolean(10) ? $this->faker->url() : null,
            ],

            // --- AUTRES RELATIONS ---
            'company_id' => null, // Remplace par Company::factory() si tu as créé la factory Company
            'program_id' => null,
            'user_id'    => null,

            // --- STATUT & DATES ---
            'status' => OfferStatus::Published,
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'expiration_date' => $this->faker->dateTimeBetween('now', '+3 months'),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::Published,
            'expiration_date' => now()->subDays(5),
        ]);
    }
}