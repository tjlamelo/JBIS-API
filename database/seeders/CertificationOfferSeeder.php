<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\CertificationOffer;
use Illuminate\Database\Seeder;

class CertificationOfferSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['fr' => 'Fabrication de ciment', 'en' => 'Cement manufacturing', 'duration_fr' => '3 mois', 'duration_en' => '3 months', 'cost' => 475000, 'first_installment' => 300000, 'second_installment' => 175000, 'sort_order' => 10],
            ['fr' => 'Agent de santé et sécurité', 'en' => 'Health and safety officer', 'duration_fr' => '2 mois', 'duration_en' => '2 months', 'cost' => 560000, 'first_installment' => 400000, 'second_installment' => 160000, 'sort_order' => 20],
            ['fr' => 'Design graphique', 'en' => 'Graphic design', 'duration_fr' => '2,5 mois', 'duration_en' => '2.5 months', 'cost' => 475000, 'first_installment' => 400000, 'second_installment' => 75000, 'sort_order' => 30],
            ['fr' => 'Photographie de base', 'en' => 'Basic photography', 'duration_fr' => '2 mois', 'duration_en' => '2 months', 'cost' => 305000, 'first_installment' => 305000, 'second_installment' => null, 'sort_order' => 40],
            ['fr' => 'Soins infirmiers', 'en' => 'Nursing care', 'duration_fr' => '4 mois', 'duration_en' => '4 months', 'cost' => 810000, 'first_installment' => 600000, 'second_installment' => 210000, 'sort_order' => 50],
            ['fr' => 'Aide-soignant', 'en' => 'Nursing assistant', 'duration_fr' => '2,5 mois', 'duration_en' => '2.5 months', 'cost' => 730000, 'first_installment' => 500000, 'second_installment' => 230000, 'sort_order' => 60],
        ];

        foreach ($rows as $row) {
            CertificationOffer::query()->updateOrCreate(
                [
                    'domain' => 'AMCA',
                    'sort_order' => $row['sort_order'],
                ],
                [
                    'title' => ['fr' => $row['fr'], 'en' => $row['en']],
                    'duration_label' => ['fr' => $row['duration_fr'], 'en' => $row['duration_en']],
                    'organization' => ['fr' => 'JBIS', 'en' => 'JBIS'],
                    'cost' => $row['cost'],
                    'first_installment' => $row['first_installment'],
                    'second_installment' => $row['second_installment'],
                    'registration_fee' => 25000,
                    'currency' => 'XAF',
                    'exam_mode' => 'ONSITE',
                    'is_active' => true,
                ],
            );
        }
    }
}
