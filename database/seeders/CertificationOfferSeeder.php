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
            ['title' => 'Fabrication de ciment', 'duration_label' => '3 mois', 'cost' => 475000, 'first_installment' => 300000, 'second_installment' => 175000, 'sort_order' => 10],
            ['title' => 'Agent de santé et sécurité', 'duration_label' => '2 mois', 'cost' => 560000, 'first_installment' => 400000, 'second_installment' => 160000, 'sort_order' => 20],
            ['title' => 'Design graphique', 'duration_label' => '2,5 mois', 'cost' => 475000, 'first_installment' => 400000, 'second_installment' => 75000, 'sort_order' => 30],
            ['title' => 'Photographie de base', 'duration_label' => '2 mois', 'cost' => 305000, 'first_installment' => 305000, 'second_installment' => null, 'sort_order' => 40],
            ['title' => 'Soins infirmiers', 'duration_label' => '4 mois', 'cost' => 810000, 'first_installment' => 600000, 'second_installment' => 210000, 'sort_order' => 50],
            ['title' => 'Aide-soignant', 'duration_label' => '2,5 mois', 'cost' => 730000, 'first_installment' => 500000, 'second_installment' => 230000, 'sort_order' => 60],
        ];

        foreach ($rows as $row) {
            CertificationOffer::query()->updateOrCreate(
                ['title' => $row['title']],
                [
                    'domain' => 'AMCA',
                    'organization' => 'JBIS',
                    'duration_label' => $row['duration_label'],
                    'cost' => $row['cost'],
                    'first_installment' => $row['first_installment'],
                    'second_installment' => $row['second_installment'],
                    'registration_fee' => 25000,
                    'currency' => 'XAF',
                    'exam_mode' => 'ONSITE',
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
