<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Location\Models\GeographicZone;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Récupération du créateur (Admin)
        $admin = User::where('email', 'admin@jbis.cm')->first();

        // 2. Récupération des zones géographiques
        $zoneAmerica = GeographicZone::where('name->en', 'North America')->first();
        $zoneEurope = GeographicZone::where('name->en', 'Europe (Non-Schengen)')->first();
        $zoneMiddleEast = GeographicZone::where('name->en', 'Middle East')->first();

        $programs = [
            [
                'name' => ['fr' => 'Entrée Express Canada', 'en' => 'Express Entry Canada'],
                'description' => [
                    'fr' => 'Accompagnement complet pour le programme d\'immigration Entrée Express au Canada.',
                    'en' => 'Full support for the Canada Express Entry immigration program.'
                ],
                'geographic_zone_id' => $zoneAmerica?->id,
                'procedure_cost' => 2300,
                'currency' => 'CAD',
                'procedure_duration' => 6,
                'duration_unit' => 'months',
                'status' => 'active',
            ],
            [
                'name' => ['fr' => 'Visa Travailleur Qualifié Albanie', 'en' => 'Skilled Worker Albania'],
                'description' => [
                    'fr' => 'Opportunités de travail en Albanie pour les profils techniques et ouvriers.',
                    'en' => 'Work opportunities in Albania for technical and worker profiles.'
                ],
                'geographic_zone_id' => $zoneEurope?->id,
                'procedure_cost' => 1500,
                'currency' => 'EUR',
                'procedure_duration' => 3,
                'duration_unit' => 'months',
                'status' => 'active',
            ],
            [
                'name' => ['fr' => 'Recrutement Hôtellerie Dubaï', 'en' => 'Dubai Hospitality Recruitment'],
                'description' => [
                    'fr' => 'Placement direct dans les grands complexes hôteliers aux Émirats Arabes Unis.',
                    'en' => 'Direct placement in major hotel complexes in the United Arab Emirates.'
                ],
                'geographic_zone_id' => $zoneMiddleEast?->id,
                'procedure_cost' => 800000,
                'currency' => 'XAF',
                'procedure_duration' => 8,
                'duration_unit' => 'weeks',
                'status' => 'active',
            ],
        ];

        foreach ($programs as $prog) {
            $uniqueSuffix = Str::random(5);
            
            Program::create([
                'user_id'            => $admin?->id,
                'geographic_zone_id' => $prog['geographic_zone_id'],
                
                // 🟢 Champs JSON (Spatie Translatable gère l'array)
                'name'               => $prog['name'],
                'description'        => $prog['description'],
                'slug'               => [
                    'fr' => Str::slug($prog['name']['fr']) . '-' . $uniqueSuffix,
                    'en' => Str::slug($prog['name']['en']) . '-' . $uniqueSuffix,
                ],

                // 🟢 Champs Standard
                'procedure_cost'     => $prog['procedure_cost'],
                'currency'           => $prog['currency'],
                'procedure_duration' => $prog['procedure_duration'],
                'duration_unit'      => $prog['duration_unit'],
                
                'required_age'       => '21 - 45 ans',
                'status'             => $prog['status'],
                'meta'               => ['seo' => ['robots' => 'index, follow']],
                
                // --- Dates ---
                'start_date'         => now()->toDateString(),
                'end_date'           => now()->addYears(2)->toDateString(),
                'published_at'       => now(),
            ]);
        }
    }
}