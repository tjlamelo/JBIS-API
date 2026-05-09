<?php

namespace Database\Seeders;

use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\Models\Offer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RequiredDocumentSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. CRÉATION DU CATALOGUE GÉNÉRIQUE ---
        // On crée les documents sans les lier directement à un ID dans cette table
        $catalog = [
            ['name' => 'Passeport Valide', 'type' => 'PDF', 'desc' => 'Copie scannée de la page biométrique.'],
            ['name' => 'Diplôme le plus élevé', 'type' => 'PDF', 'desc' => 'Diplôme certifié conforme.'],
            ['name' => 'Test de langue (IELTS/TEF)', 'type' => 'PDF', 'desc' => 'Résultats de moins de 2 ans.'],
            ['name' => 'Preuve de fonds', 'type' => 'PDF', 'desc' => 'Attestation bancaire récente.'],
            ['name' => 'Certificat de visite médicale', 'type' => 'IMAGE', 'desc' => 'Scanner du test de vue et aptitude physique.'],
            ['name' => 'Portfolio / GitHub', 'type' => 'OTHER', 'desc' => 'Document contenant les liens vers vos projets.'],
            ['name' => 'Casier Judiciaire', 'type' => 'PDF', 'desc' => 'Extrait de moins de 3 mois.'],
        ];

        foreach ($catalog as $item) {
            RequiredDocument::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'description' => $item['desc']
                ]
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
                'preuve-de-fonds'
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
                'certificat-de-visite-medicale'
            ])->pluck('id');

            $progDubai->requiredDocuments()->sync(
                $dubaiDocs->values()->mapWithKeys(fn ($id, $index) => [
                    $id => ['is_mandatory' => true, 'sort_order' => $index + 1],
                ])->toArray()
            );
        }

        // --- 3. LIAISON AUX OFFRES (Via Pivot) ---

        // Offre Développeur
        $offerDev = Offer::where('title->fr', 'LIKE', '%Développeur%')->first();
        if ($offerDev) {
            $devDocs = RequiredDocument::whereIn('slug', [
                'passeport-valide',
                'diplome-le-plus-eleve',
                'portfolio-github'
            ])->get()->values()->mapWithKeys(function ($doc, $index) {
                // Exemple : On rend le portfolio optionnel via le pivot
                return [$doc->id => [
                    'is_mandatory' => ($doc->slug !== 'portfolio-github'),
                    'sort_order' => $index + 1,
                ]];
            });

            $offerDev->requiredDocuments()->sync($devDocs);
        }
    }
}