<?php

namespace Database\Factories;

use App\Core\Domain\Catalog\Models\ContractType;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\OfferCategory;
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

        $titleFr = $this->faker->jobTitle().' (H/F)';
        $titleEn = $this->faker->jobTitle();
        $uniqueSuffix = Str::random(5);

        return [
            'title' => [
                'fr' => $titleFr,
                'en' => $titleEn,
            ],
            'description' => [
                'fr' => 'Description en français : '.$this->faker->paragraph(4),
                'en' => 'English description: '.$this->faker->paragraph(4),
            ],
            'slug' => [
                'fr' => Str::slug($titleFr.'-'.$uniqueSuffix),
                'en' => Str::slug($titleEn.'-'.$uniqueSuffix),
            ],
            'address' => $this->faker->streetAddress(),
            'work_mode' => $this->faker->randomElement(['on-site', 'hybrid', 'remote']),
            'country_id' => Country::query()->inRandomOrder()->value('id'),
            'offer_category_id' => OfferCategory::query()->inRandomOrder()->value('id'),
            'contract_type_id' => ContractType::query()->inRandomOrder()->value('id'),
            'city_id' => null,
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'currency' => $this->faker->randomElement(['EUR', 'CAD', 'USD', 'XAF']),
            'is_salary_public' => $this->faker->boolean(80),
            'is_company_public' => $this->faker->boolean(85),
            'available_positions' => $this->faker->numberBetween(1, 5),
            'meta' => [
                'is_featured' => $this->faker->boolean(15),
                'is_urgent' => $this->faker->boolean(10),
                'seo' => [
                    'title' => 'Recrutement : '.$titleFr,
                    'description' => 'Postulez dès maintenant pour ce poste sur la plateforme JBIS.',
                    'robots' => 'index, follow',
                ],
                'external_link' => $this->faker->boolean(10) ? $this->faker->url() : null,
            ],
            'company_id' => null,
            'program_id' => null,
            'user_id' => null,
            'offer_type_id' => null,
            'work_schedule_id' => null,
            'education_level_id' => null,
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
