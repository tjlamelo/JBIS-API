<?php

namespace Database\Seeders;

use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RequiredDocumentSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. CRÉATION DU CATALOGUE GÉNÉRIQUE ---
        // On crée les documents sans les lier directement à un ID dans cette table
        $catalog = [
            [
                'name' => ['fr' => 'Passeport valide', 'en' => 'Valid passport'],
                'slug' => 'passeport-valide',
                'type' => 'PDF',
                'description' => 'Copie scannée de la page biométrique.',
            ],
            [
                'name' => ['fr' => 'Diplôme le plus élevé', 'en' => 'Highest diploma'],
                'slug' => 'diplome-le-plus-eleve',
                'type' => 'PDF',
                'description' => 'Diplôme certifié conforme.',
            ],
            [
                'name' => ['fr' => 'Test de langue (IELTS/TEF)', 'en' => 'Language test (IELTS/TEF)'],
                'slug' => 'test-de-langue-ieltstef',
                'type' => 'PDF',
                'description' => 'Résultats de moins de 2 ans.',
            ],
            [
                'name' => ['fr' => 'Preuve de fonds', 'en' => 'Proof of funds'],
                'slug' => 'preuve-de-fonds',
                'type' => 'PDF',
                'description' => 'Attestation bancaire récente.',
            ],
            [
                'name' => ['fr' => 'Certificat de visite médicale', 'en' => 'Medical examination certificate'],
                'slug' => 'certificat-de-visite-medicale',
                'type' => 'IMAGE',
                'description' => 'Scanner du test de vue et aptitude physique.',
            ],
            [
                'name' => ['fr' => 'Casier judiciaire', 'en' => 'Criminal record'],
                'slug' => 'casier-judiciaire',
                'type' => 'PDF',
                'description' => 'Extrait de moins de 3 mois.',
            ],
            [
                'name' => ['fr' => 'Curriculum Vitae (CV)', 'en' => 'Resume (CV)'],
                'slug' => 'cv',
                'type' => 'PDF',
                'description' => 'CV à jour, format standard.',
            ],
            [
                'name' => ['fr' => 'Lettre de motivation', 'en' => 'Cover letter'],
                'slug' => 'lettre-motivation',
                'type' => 'PDF',
                'description' => 'Lettre expliquant vos motivations.',
            ],
            [
                'name' => ['fr' => 'Portfolio', 'en' => 'Portfolio'],
                'slug' => 'portfolio',
                'type' => 'PDF',
                'description' => 'Lien ou extraits de projets.',
            ],
            [
                'name' => ['fr' => 'Photo d’identité récente', 'en' => 'Recent ID photo'],
                'slug' => 'photo-identite',
                'type' => 'IMAGE',
                'description' => 'Photo format passeport.',
            ],
            [
                'name' => ['fr' => 'Extrait de naissance', 'en' => 'Birth certificate'],
                'slug' => 'extrait-de-naissance',
                'type' => 'PDF',
                'description' => 'Copie intégrale ou extrait avec filiation.',
            ],
            [
                'name' => ['fr' => 'Justificatif de domicile', 'en' => 'Proof of residence'],
                'slug' => 'justificatif-domicile',
                'type' => 'PDF',
                'description' => 'Facture ou attestation de moins de 3 mois.',
            ],
            // Ajoute ici d'autres documents si besoin
        ];

        foreach ($catalog as $item) {
            $slug = $item['slug'] ?? Str::slug($item['name']['fr'] ?? $item['name']['en'] ?? 'document');

            RequiredDocument::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => json_encode($item['name'], JSON_UNESCAPED_UNICODE),
                    'type' => $item['type'],
                    'description' => $item['description'] ?? null,
                ],
            );
        }

        // --- 2. LIAISON AUX PROGRAMMES (Via Pivot) ---

        // Programme Canada (Entrée Express)
        $progCanada = Program::where('name->fr', 'LIKE', '%Express%')->first();
        if ($progCanada) {
            $canadaDocs = RequiredDocument::whereIn('slug', [
                'passeport-valide',
                'diplome-le-plus-eleve',
                'test-de-langue-ieltstef',
                'preuve-de-fonds',
            ])->pluck('id');

            $progCanada->requiredDocuments()->sync(
                $canadaDocs->values()->mapWithKeys(fn ($id, $index) => [
                    $id => ['is_mandatory' => true, 'sort_order' => $index + 1],
                ])->toArray()
            );
        }

        // Programme Dubaï
        $progDubai = Program::where('name->fr', 'LIKE', '%Dubaï%')->first();
        if ($progDubai) {
            $dubaiDocs = RequiredDocument::whereIn('slug', [
                'passeport-valide',
                'certificat-de-visite-medicale',
            ])->pluck('id');

            $progDubai->requiredDocuments()->sync(
                $dubaiDocs->values()->mapWithKeys(fn ($id, $index) => [
                    $id => ['is_mandatory' => true, 'sort_order' => $index + 1],
                ])->toArray()
            );
        }

        // --- 3. LIAISON AUX OFFRES (Via Pivot) ---

        // Offre Développeur
        $offerDev = Offer::query()
            ->whereHas('trade', fn ($query) => $query->where('slug', 'full-stack-developer'))
            ->first();
        if ($offerDev) {
            $devDocs = RequiredDocument::whereIn('slug', [
                'passeport-valide',
                'diplome-le-plus-eleve',
                'portfolio',
            ])->get()->values()->mapWithKeys(function ($doc, $index) {
                return [$doc->id => [
                    'is_mandatory' => $doc->slug !== 'portfolio',
                    'sort_order' => $index + 1,
                ]];
            });

            $offerDev->requiredDocuments()->sync($devDocs);
        }
    }
}
