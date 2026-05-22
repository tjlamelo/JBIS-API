<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Training;
use App\Core\Domain\Catalog\States\TrainingDeliveryMode;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'domain' => 'Langues',
                'title' => 'Anglais général — niveau A2',
                'organization' => 'JBIS Language Center',
                'description' => 'Cours d\'anglais pour candidats en mobilité : bases, entretien, vie quotidienne.',
                'duration_hours' => 40,
                'mode' => TrainingDeliveryMode::Hybrid->value,
                'location' => 'Yaoundé / en ligne',
                'is_certified' => true,
                'certificate_name' => 'Attestation JBIS A2',
            ],
            [
                'domain' => 'Langues',
                'title' => 'Anglais professionnel — B1',
                'organization' => 'JBIS Language Center',
                'description' => 'Expression orale et écrite pour le monde du travail à l\'étranger.',
                'duration_hours' => 60,
                'mode' => TrainingDeliveryMode::Online->value,
                'location' => 'https://learn.jbis.cm',
                'is_certified' => true,
                'certificate_name' => 'Attestation JBIS B1',
            ],
            [
                'domain' => 'Langues',
                'title' => 'Français renforcement — FLE',
                'organization' => 'JBIS Language Center',
                'description' => 'Perfectionnement pour dossiers Canada / France / Belgique.',
                'duration_hours' => 30,
                'mode' => TrainingDeliveryMode::Onsite->value,
                'location' => 'Agence Bastos',
                'is_certified' => false,
            ],
        ];

        foreach ($items as $item) {
            Training::updateOrCreate(
                ['title' => $item['title'], 'organization' => $item['organization']],
                [...$item, 'is_active' => true],
            );
        }
    }
}
