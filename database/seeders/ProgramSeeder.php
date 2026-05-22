<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Location\Models\GeographicZone;
use App\Core\Domain\Location\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@jbis.cm')->first();

        $zoneAmerica = GeographicZone::where('name->en', 'North America')->first();
        $zoneEurope = GeographicZone::where('name->en', 'Europe (Non-Schengen)')->first();
        $zoneMiddleEast = GeographicZone::where('name->en', 'Middle East')->first();

        $programs = [
            [
                'name' => ['fr' => 'Entrée Express Canada', 'en' => 'Express Entry Canada'],
                'description' => [
                    'fr' => 'Accompagnement complet pour le programme d\'immigration Entrée Express au Canada.',
                    'en' => 'Full support for the Canada Express Entry immigration program.',
                ],
                'geographic_zone_id' => $zoneAmerica?->id,
                'procedure_duration' => 6,
                'duration_unit' => 'months',
                'age_min' => 21,
                'age_max' => 45,
                'is_featured' => true,
                'is_urgent' => false,
                'views_count' => 1280,
                'status' => 'PUBLISHED',
            ],
            [
                'name' => ['fr' => 'Visa Travailleur Qualifié Albanie', 'en' => 'Skilled Worker Albania'],
                'description' => [
                    'fr' => 'Opportunités de travail en Albanie pour les profils techniques et ouvriers.',
                    'en' => 'Work opportunities in Albania for technical and worker profiles.',
                ],
                'geographic_zone_id' => $zoneEurope?->id,
                'procedure_duration' => 3,
                'duration_unit' => 'months',
                'age_min' => 20,
                'age_max' => 50,
                'is_featured' => false,
                'is_urgent' => false,
                'views_count' => 412,
                'status' => 'PUBLISHED',
            ],
            [
                'name' => ['fr' => 'Recrutement Hôtellerie Dubaï', 'en' => 'Dubai Hospitality Recruitment'],
                'description' => [
                    'fr' => 'Placement direct dans les grands complexes hôteliers aux Émirats Arabes Unis.',
                    'en' => 'Direct placement in major hotel complexes in the United Arab Emirates.',
                ],
                'geographic_zone_id' => $zoneMiddleEast?->id,
                'procedure_duration' => 8,
                'duration_unit' => 'weeks',
                'age_min' => 18,
                'age_max' => 40,
                'is_featured' => false,
                'is_urgent' => true,
                'views_count' => 3502,
                'status' => 'PUBLISHED',
            ],
            [
                'name' => ['fr' => 'Programme pilote Afrique centrale', 'en' => 'Central Africa pilot program'],
                'description' => [
                    'fr' => 'Programme en préparation — données de démonstration (brouillon).',
                    'en' => 'Program in preparation — demo data (draft).',
                ],
                'geographic_zone_id' => $zoneMiddleEast?->id,
                'procedure_duration' => null,
                'duration_unit' => 'months',
                'age_min' => null,
                'age_max' => null,
                'is_featured' => false,
                'is_urgent' => false,
                'views_count' => 0,
                'status' => 'DRAFT',
            ],
        ];

        $seeded = [];

        foreach ($programs as $prog) {
            $slugFr = Str::slug($prog['name']['fr']);
            $slugEn = Str::slug($prog['name']['en']);

            $publishedAt = ($prog['status'] ?? 'PUBLISHED') === 'PUBLISHED' ? now() : null;

            $seeded[] = Program::query()->updateOrCreate(
                [
                    'slug->fr' => $slugFr,
                ],
                [
                    'user_id' => $admin?->id,
                    'geographic_zone_id' => $prog['geographic_zone_id'],
                    'name' => $prog['name'],
                    'description' => $prog['description'],
                    'slug' => [
                        'fr' => $slugFr,
                        'en' => $slugEn,
                    ],
                    'procedure_duration' => $prog['procedure_duration'],
                    'duration_unit' => $prog['duration_unit'],
                    'age_min' => $prog['age_min'],
                    'age_max' => $prog['age_max'],
                    'is_featured' => $prog['is_featured'],
                    'is_urgent' => $prog['is_urgent'],
                    'views_count' => $prog['views_count'],
                    'status' => $prog['status'],
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addYears(2)->toDateString(),
                    'published_at' => $publishedAt,
                ]
            );
        }

        $langFr = Language::query()->where('code', 'fr')->first();
        $langEn = Language::query()->where('code', 'en')->first();

        if ($langFr && $langEn) {
            foreach ($seeded as $program) {
                $program->languages()->sync([
                    $langFr->id => ['is_mandatory' => true],
                    $langEn->id => ['is_mandatory' => false],
                ]);
            }
        }
    }
}
