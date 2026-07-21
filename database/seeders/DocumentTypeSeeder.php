<?php

namespace Database\Seeders;

use App\Core\Domain\Identity\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'unique_per_user' => false,
            'requires_expiry_date' => false,
            'requires_document_number' => false,
            'visible_to_candidates' => true,
            'allowed_extensions' => DocumentType::defaultAllowedExtensions(),
            'allowed_mime_types' => DocumentType::defaultAllowedMimeTypes(),
            'is_active' => true,
        ];

        $catalog = [
            // === IDENTITÉ & ÉTAT CIVIL ===
            [
                'code' => 'PASSPORT',
                'label' => ['fr' => 'Passeport', 'en' => 'Passport'],
                'storage_slug' => 'passeport',
                'unique_per_user' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 10,
            ],
            [
                'code' => 'ID_CARD',
                'label' => ['fr' => 'Carte nationale d’identité', 'en' => 'National ID card'],
                'storage_slug' => 'carte-identite',
                'unique_per_user' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 20,
            ],
            [
                'code' => 'BIRTH_CERTIFICATE',
                'label' => ['fr' => 'Acte de naissance', 'en' => 'Birth certificate'],
                'storage_slug' => 'acte-naissance',
                'unique_per_user' => true,
                'requires_document_number' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 30,
            ],
            [
                'code' => 'MARRIAGE_CERTIFICATE',
                'label' => ['fr' => 'Acte de mariage', 'en' => 'Marriage certificate'],
                'storage_slug' => 'acte-mariage',
                'unique_per_user' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 35,
            ],
            [
                'code' => 'DRIVING_LICENSE',
                'label' => ['fr' => 'Permis de conduire', 'en' => 'Driving license'],
                'storage_slug' => 'permis-conduire',
                'unique_per_user' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 40,
            ],
            [
                'code' => 'RESIDENCE_PERMIT',
                'label' => ['fr' => 'Titre de séjour', 'en' => 'Residence permit'],
                'storage_slug' => 'titre-sejour',
                'unique_per_user' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 50,
            ],
            [
                'code' => 'VISA',
                'label' => ['fr' => 'Visa', 'en' => 'Visa'],
                'storage_slug' => 'visa',
                'unique_per_user' => true,
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 60,
            ],

            // === RECRUTEMENT & CARRIÈRE ===
            [
                'code' => 'CV',
                'label' => ['fr' => 'CV', 'en' => 'Resume'],
                'storage_slug' => 'cv',
                'unique_per_user' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 70,
            ],
            [
                'code' => 'COVER_LETTER',
                'label' => ['fr' => 'Lettre de motivation', 'en' => 'Cover letter'],
                'storage_slug' => 'lettre-motivation',
                'max_file_size_kb' => 10240,
                'sort_order' => 80,
            ],
            [
                'code' => 'DIPLOMA',
                'label' => ['fr' => 'Diplôme', 'en' => 'Diploma'],
                'storage_slug' => 'diplome',
                'max_file_size_kb' => 10240,
                'sort_order' => 90,
            ],
            [
                'code' => 'TRANSCRIPT',
                'label' => ['fr' => 'Relevé de notes', 'en' => 'Transcript'],
                'storage_slug' => 'releve-notes',
                'max_file_size_kb' => 10240,
                'sort_order' => 100,
            ],
            [
                'code' => 'WORK_CERTIFICATE',
                'label' => ['fr' => 'Attestation de travail', 'en' => 'Work certificate'],
                'storage_slug' => 'attestation-travail',
                'max_file_size_kb' => 10240,
                'sort_order' => 110,
            ],
            [
                'code' => 'EMPLOYMENT_CONTRACT',
                'label' => ['fr' => 'Contrat de travail', 'en' => 'Employment contract'],
                'storage_slug' => 'contrat-travail',
                'max_file_size_kb' => 10240,
                'sort_order' => 120,
                'visible_to_candidates' => false,
            ],
            [
                'code' => 'PROFESSIONAL_CERTIFICATION',
                'label' => ['fr' => 'Certification professionnelle', 'en' => 'Professional certification'],
                'storage_slug' => 'certification-pro',
                'max_file_size_kb' => 10240,
                'sort_order' => 130,
            ],
            [
                'code' => 'PROFESSIONAL_CARD',
                'label' => ['fr' => 'Carte professionnelle', 'en' => 'Professional card'],
                'storage_slug' => 'carte-pro',
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 140,
            ],
            [
                'code' => 'RECOMMENDATION_LETTER',
                'label' => ['fr' => 'Lettre de recommandation', 'en' => 'Recommendation letter'],
                'storage_slug' => 'lettre-recommandation',
                'max_file_size_kb' => 10240,
                'sort_order' => 150,
            ],

            // === SANTÉ & SÉCURITÉ SOCIALE ===
            [
                'code' => 'MEDICAL_CERTIFICATE',
                'label' => ['fr' => 'Certificat médical', 'en' => 'Medical certificate'],
                'storage_slug' => 'certificat-medical',
                'requires_expiry_date' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 160,
            ],
            [
                'code' => 'HEALTH_INSURANCE_CARD',
                'label' => ['fr' => 'Carte d’assurance maladie', 'en' => 'Health insurance card'],
                'storage_slug' => 'carte-mutuelle',
                'requires_expiry_date' => true,
                'requires_document_number' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 170,
            ],
            [
                'code' => 'VACCINATION_RECORD',
                'label' => ['fr' => 'Carnet de vaccination', 'en' => 'Vaccination record'],
                'storage_slug' => 'carnet-vaccination',
                'max_file_size_kb' => 10240,
                'sort_order' => 180,
            ],

            // === JUSTIFICATIFS & ADMINISTRATIF ===
            [
                'code' => 'RESIDENCE_PROOF',
                'label' => ['fr' => 'Justificatif de domicile', 'en' => 'Proof of residence'],
                'storage_slug' => 'justificatif-domicile',
                'max_file_size_kb' => 5120,
                'sort_order' => 190,
            ],
            [
                'code' => 'CRIMINAL_RECORD',
                'label' => ['fr' => 'Casier judiciaire', 'en' => 'Criminal record'],
                'storage_slug' => 'casier-judiciaire',
                'unique_per_user' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 200,
            ],
            [
                'code' => 'TAX_ID',
                'label' => ['fr' => 'Numéro d’identification fiscale', 'en' => 'Tax identification number'],
                'storage_slug' => 'nif',
                'requires_document_number' => true,
                'max_file_size_kb' => 5120,
                'sort_order' => 210,
            ],
            [
                'code' => 'BANK_ACCOUNT_PROOF',
                'label' => ['fr' => 'RIB / Relevé d’identité bancaire', 'en' => 'Bank account statement'],
                'storage_slug' => 'rib',
                'max_file_size_kb' => 5120,
                'sort_order' => 220,
                'visible_to_candidates' => false,
            ],
            [
                'code' => 'PARENTAL_CONSENT',
                'label' => ['fr' => 'Autorisation parentale', 'en' => 'Parental consent'],
                'storage_slug' => 'autorisation-parentale',
                'max_file_size_kb' => 5120,
                'sort_order' => 230,
            ],
            [
                'code' => 'RECEIPT',
                'label' => ['fr' => 'Reçu', 'en' => 'Receipt'],
                'storage_slug' => 'recu',
                'max_file_size_kb' => 5120,
                'sort_order' => 235,
            ],
            [
                'code' => 'AGREEMENT_PROTOCOL',
                'label' => ['fr' => "Protocole d'accord", 'en' => 'Agreement protocol'],
                'storage_slug' => 'protocole-accord',
                'unique_per_user' => true,
                'max_file_size_kb' => 10240,
                'sort_order' => 240,
            ],

            // === PHOTOS & MULTIMÉDIA ===
            [
                'code' => 'PHOTO',
                'label' => ['fr' => "Photo d'identité", 'en' => 'ID photo'],
                'storage_slug' => 'photo-identite',
                'unique_per_user' => true,
                'max_file_size_kb' => 5120,
                'sort_order' => 250,
                'visible_to_candidates' => false,
            ],

            // === FORMATION & STAGES ===
            [
                'code' => 'INTERNSHIP_AGREEMENT',
                'label' => ['fr' => 'Convention de stage', 'en' => 'Internship agreement'],
                'storage_slug' => 'convention-stage',
                'max_file_size_kb' => 10240,
                'sort_order' => 260,
            ],
            [
                'code' => 'TRAINING_CERTIFICATE',
                'label' => ['fr' => 'Attestation de formation', 'en' => 'Training certificate'],
                'storage_slug' => 'attestation-formation',
                'max_file_size_kb' => 10240,
                'sort_order' => 270,
            ],

            // === AUTRES ===
            [
                'code' => 'OTHER',
                'label' => ['fr' => 'Autre document', 'en' => 'Other document'],
                'storage_slug' => 'autre',
                'max_file_size_kb' => 10240,
                'sort_order' => 999,
            ],
        ];
        // Fusionner les defaults : toutes les lignes doivent avoir les mêmes clés (requis pour upsert).
        $data = array_map(static function (array $item) use ($defaults): array {
            $row = array_merge($defaults, $item);

            $row['label'] = json_encode($row['label'], JSON_UNESCAPED_UNICODE);
            $row['allowed_extensions'] = json_encode($row['allowed_extensions'], JSON_UNESCAPED_UNICODE);
            $row['allowed_mime_types'] = json_encode($row['allowed_mime_types'], JSON_UNESCAPED_UNICODE);

            return $row;
        }, $catalog);

        DocumentType::flushEventListeners();

        DocumentType::upsert(
            $data,
            ['code'], // colonne(s) unique(s)
            [         // colonnes à mettre à jour en cas de conflit
                'label',
                'storage_slug',
                'unique_per_user',
                'requires_expiry_date',
                'requires_document_number',
                'max_file_size_kb',
                'allowed_extensions',
                'allowed_mime_types',
                'sort_order',
                'is_active',
                'visible_to_candidates',
            ]
        );
    }
}
