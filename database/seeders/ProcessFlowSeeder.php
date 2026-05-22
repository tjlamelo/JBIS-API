<?php

namespace Database\Seeders;

use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Services\ProcessFlow\ProcessFlowFeeRecalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ProcessFlowSeeder — templates de parcours procéduraux (abstraits).
 *
 * Chaque enregistrement est un modèle versionné indépendant : aucun program_id,
 * offer_id ni country_id n'est renseigné ici. Les libellés « Albanie », « Serbie », etc.
 * décrivent le contenu documentaire source, pas un lien FK obligatoire.
 *
 * Structure :
 *   - ProcessFlow         : entête (flow_group_id, version, status published)
 *   - ProcessFlowSection  : blocs UI (clé stable + titre bilingue)
 *   - ProcessStep         : étapes typées (DOCUMENT_COLLECTION, PAYMENT, …)
 *
 * Nouvelle version : ProcessFlow::cloneAsNewVersion() puis publish().
 */
class ProcessFlowSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedAlbania();
            $this->seedSerbia();
            $this->seedCanadaRP();
            $this->linkPublishedFlowsToCatalog();
        });
    }

    /**
     * Lie au moins un parcours publié à une offre/programme pour l'inscription auto (sans process_flow_id manuel).
     */
    private function linkPublishedFlowsToCatalog(): void
    {
        $offerId = DB::table('offers')->orderBy('id')->value('id');
        $programId = DB::table('programs')->orderBy('id')->value('id');

        if ($offerId === null) {
            return;
        }

        $albaniaFlowId = DB::table('process_flows')
            ->where('status', 'published')
            ->where('name', 'like', '%Albanie%')
            ->orderByDesc('version')
            ->value('id');

        if ($albaniaFlowId !== null) {
            DB::table('process_flows')->where('id', $albaniaFlowId)->update([
                'offer_id' => $offerId,
                'program_id' => $programId,
                'updated_at' => now(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // ALBANIE
    // -------------------------------------------------------------------------
    private function seedAlbania(): void
    {
        $flowGroupId = Str::uuid()->toString();

        $flow = DB::table('process_flows')->insertGetId([
            'flow_group_id'          => $flowGroupId,
            'version'                => 1,
            'status'                 => 'published',
            'name'                   => json_encode(['fr' => 'Processus Albanie', 'en' => 'Albania Process']),
            'description'            => json_encode([
                'fr' => 'Procédure de placement professionnel vers l\'Albanie via JBIS.',
                'en' => 'Professional placement procedure to Albania via JBIS.',
            ]),
            'file_opening_fee'       => 0,
            'total_procedure_fees'   => 0,
            'estimated_duration_days'=> 120,
            'internal_notes'         => 'Même structure que Serbie. Entretien en anglais obligatoire.',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $this->createAlbaniaSerbiaSections($flow, 'albania');
        $this->recalculateFlowFees($flow);
    }

    // -------------------------------------------------------------------------
    // SERBIE
    // -------------------------------------------------------------------------
    private function seedSerbia(): void
    {
        $flowGroupId = Str::uuid()->toString();

        $flow = DB::table('process_flows')->insertGetId([
            'flow_group_id'          => $flowGroupId,
            'version'                => 1,
            'status'                 => 'published',
            'name'                   => json_encode(['fr' => 'Processus Serbie', 'en' => 'Serbia Process']),
            'description'            => json_encode([
                'fr' => 'Procédure de placement professionnel vers la Serbie via JBIS.',
                'en' => 'Professional placement procedure to Serbia via JBIS.',
            ]),
            'file_opening_fee'       => 0,
            'total_procedure_fees'   => 0,
            'estimated_duration_days'=> 120,
            'internal_notes'         => 'Même structure que Albanie. Entretien en anglais obligatoire.',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $this->createAlbaniaSerbiaSections($flow, 'serbia');
        $this->recalculateFlowFees($flow);
    }

    // -------------------------------------------------------------------------
    // Sections et étapes partagées Albanie / Serbie
    // -------------------------------------------------------------------------
    private function createAlbaniaSerbiaSections(int $flowId, string $context): void
    {
        // ----- SECTION 1 : Ouverture de dossier -----
        $s1 = $this->insertSection($flowId, [
            'key'           => 'file_opening',
            'title'         => ['fr' => 'Ouverture de dossier', 'en' => 'File opening'],
            'description'   => ['fr' => 'Documents à fournir pour ouvrir le dossier candidat.', 'en' => 'Documents required to open the candidate file.'],
            'section_order' => 1,
            'icon'          => 'ti-folder-open',
            'color'         => '#5B4FCF',
        ]);

        $this->insertSteps($flowId, $s1, [
            [
                'step_type'         => 'DOCUMENT_COLLECTION',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => 'Dépôt des documents d\'ouverture', 'en' => 'Initial document submission'],
                'description'       => ['fr' => 'Le candidat remet : formulaire entreprise, CV, CNI/acte de naissance/diplômes, photo fond blanc, vidéo de présentation 30s. Des documents supplémentaires peuvent être exigés.', 'en' => 'Candidate submits: company form, CV, ID/birth certificate/academic & work certificates, white background photo, 30-second presentation video. Supplementary documents may be required.'],
                'requires_documents'        => true,
                'document_type_ids'         => $this->documentTypeIdsByCodes(['CV', 'ID_CARD', 'BIRTH_CERTIFICATE', 'DIPLOMA', 'PHOTO']),
                'is_blocking'       => true,
                'step_order'        => 1,
            ],
            [
                'step_type'         => 'PAYMENT',
                'payment_type'      => 'FILE_OPENING',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => 'Paiement frais d\'ouverture de dossier', 'en' => 'File opening fee payment'],
                'description'       => ['fr' => 'Versement obligatoire de 300 000 FCFA pour la présélection. Étape non remboursable.', 'en' => 'Mandatory payment of 300,000 CFA F for the pre-selection stage. Non-refundable.'],
                'default_amount'    => 300000,
                'is_blocking'       => true,
                'step_order'        => 2,
            ],
        ]);

        // ----- SECTION 2 : Suivi de procédure -----
        $s2 = $this->insertSection($flowId, [
            'key'           => 'procedure_followup',
            'title'         => ['fr' => 'Suivi de procédure', 'en' => 'Procedure follow-up'],
            'description'   => ['fr' => 'Évaluation du profil et présentation aux employeurs.', 'en' => 'Profile evaluation and presentation to employers.'],
            'section_order' => 2,
            'icon'          => 'ti-clipboard-list',
            'color'         => '#0F6E56',
        ]);

        $this->insertSteps($flowId, $s2, [
            [
                'step_type'         => 'SERVICE',
                'responsible_party' => 'JBIS',
                'title'             => ['fr' => 'Cours d\'anglais et évaluation du profil', 'en' => 'English classes and profile evaluation'],
                'description'       => ['fr' => 'JBIS dispense des cours d\'anglais au candidat, évalue son profil, traduit ses documents en anglais et présente son profil aux employeurs partenaires.', 'en' => 'JBIS provides English classes, evaluates the candidate profile, translates documents into English, and presents the profile to partner employers.'],
                'responsible_party' => 'JBIS',
                'is_blocking'       => false,
                'step_order'        => 1,
            ],
            [
                'step_type'         => 'PAYMENT',
                'payment_type'      => 'PROCEDURE_INSTALMENT',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => '1er versement procédure', 'en' => '1st procedure instalment'],
                'description'       => ['fr' => 'Versement de 1 500 000 FCFA dans une banque partenaire JBIS (ORIS FINANCE ou SCB Cameroun) après approbation de l\'employeur.', 'en' => 'Payment of 1,500,000 CFA F at a JBIS partner bank (ORIS FINANCE or SCB Cameroun) after employer approval.'],
                'default_amount'    => 1500000,
                'accepted_banks'    => ['ORIS_FINANCE', 'SCB'],
                'is_blocking'       => true,
                'step_order'        => 2,
            ],
            [
                'step_type'         => 'PAYMENT',
                'payment_type'      => 'BLOCKED_ACCOUNT',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => '2ème versement — compte bloqué', 'en' => '2nd instalment — blocked account'],
                'description'       => ['fr' => 'Versement de 1 500 000 FCFA en compte bloqué (ORIS FINANCE ou SCB). Ce montant est libéré à JBIS uniquement à l\'obtention du visa.', 'en' => 'Payment of 1,500,000 CFA F into a blocked account (ORIS FINANCE or SCB). This amount is released to JBIS only upon visa issuance.'],
                'default_amount'    => 1500000,
                'accepted_banks'    => ['ORIS_FINANCE', 'SCB'],
                'internal_note'     => 'Compte bloqué — ne pas confondre avec le versement libre.',
                'is_blocking'       => true,
                'step_order'        => 3,
            ],
        ]);

        // ----- SECTION 3 : Entretien d'embauche -----
        $s3 = $this->insertSection($flowId, [
            'key'           => 'job_interview',
            'title'         => ['fr' => 'Entretien d\'embauche', 'en' => 'Job interview with employer'],
            'description'   => ['fr' => 'Entretien en anglais avec l\'employeur étranger.', 'en' => 'Interview in English with the foreign employer.'],
            'section_order' => 3,
            'icon'          => 'ti-briefcase',
            'color'         => '#185FA5',
        ]);

        $this->insertSteps($flowId, $s3, [
            [
                'step_type'         => 'INFO',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => 'Préparation à l\'entretien en anglais', 'en' => 'English interview preparation'],
                'description'       => ['fr' => 'Tous les candidats francophones doivent maîtriser l\'anglais pour l\'entretien. Durant l\'entretien, le candidat peut négocier son salaire.', 'en' => 'All French-speaking candidates must learn English for the interview. During the interview, candidates can negotiate their salary.'],
                'is_blocking'       => false,
                'step_order'        => 1,
            ],
            [
                'step_type'         => 'INTERVIEW',
                'responsible_party' => 'EMPLOYER',
                'title'             => ['fr' => 'Entretien avec l\'employeur', 'en' => 'Interview with employer'],
                'description'       => ['fr' => 'Entretien de recrutement mené par l\'employeur étranger.', 'en' => 'Recruitment interview conducted by the foreign employer.'],
                'is_blocking'       => true,
                'step_order'        => 2,
            ],
            [
                'step_type'         => 'SIGNING',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => 'Signature du contrat de travail et du permis', 'en' => 'Work contract and work permit signing'],
                'description'       => ['fr' => 'Signature du contrat de travail et du permis de travail par le candidat retenu.', 'en' => 'Signing of the work contract and work permit by the selected candidate.'],
                'is_blocking'       => true,
                'step_order'        => 3,
            ],
        ]);

        // ----- SECTION 4 : Compléments de dossier post-entretien -----
        $s4 = $this->insertSection($flowId, [
            'key'                       => 'post_interview_docs',
            'title'                     => ['fr' => 'Compléments de dossier après l\'entretien', 'en' => 'Supplementary documents after interview'],
            'description'               => ['fr' => 'Documents à déposer par le candidat après l\'entretien d\'embauche.', 'en' => 'Documents to be submitted by the candidate after the job interview.'],
            'section_order'             => 4,
            'icon'                      => 'ti-file-plus',
            'color'                     => '#993C1D',
            'visible_after_section_key' => 'job_interview',
        ]);

        $this->insertSteps($flowId, $s4, [
            [
                'step_type'                 => 'DOCUMENT_COLLECTION',
                'responsible_party'         => 'CANDIDATE',
                'title'                     => ['fr' => 'Dépôt des documents complémentaires', 'en' => 'Supplementary document submission'],
                'description'               => ['fr' => 'Le candidat dépose les documents requis après validation de l\'entretien.', 'en' => 'Candidate submits required documents after interview validation.'],
                'requires_documents'        => true,
                'document_type_ids'         => $this->documentTypeIdsByCodes(['PASSPORT', 'BIRTH_CERTIFICATE', 'PHOTO']),
                'is_blocking'               => true,
                'step_order'                => 1,
            ],
        ]);

        // ----- SECTION 5 : Sortie du visa -----
        $s5 = $this->insertSection($flowId, [
            'key'                       => 'visa_exit',
            'title'                     => ['fr' => 'Sortie du visa', 'en' => 'Visa issuance'],
            'description'               => ['fr' => 'Obtention du visa et libération du compte bloqué.', 'en' => 'Visa issuance and release of the blocked account.'],
            'section_order'             => 5,
            'icon'                      => 'ti-plane-departure',
            'color'                     => '#3B6D11',
            'visible_after_section_key' => 'post_interview_docs',
        ]);

        $this->insertSteps($flowId, $s5, [
            [
                'step_type'         => 'IMMIGRATION_EXIT',
                'responsible_party' => 'AUTHORITY',
                'title'             => ['fr' => 'Obtention du visa', 'en' => 'Visa issuance'],
                'description'       => ['fr' => 'Le visa est délivré par les autorités consulaires. À l\'obtention, le montant bloqué de 1 500 000 FCFA est libéré à JBIS.', 'en' => 'The visa is issued by consular authorities. Upon issuance, the blocked amount of 1,500,000 CFA F is released to JBIS.'],
                'is_blocking'       => true,
                'step_order'        => 1,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // CANADA — Résidence Permanente
    // -------------------------------------------------------------------------
    private function seedCanadaRP(): void
    {
        $flowGroupId = Str::uuid()->toString();

        $flow = DB::table('process_flows')->insertGetId([
            'flow_group_id'          => $flowGroupId,
            'version'                => 1,
            'status'                 => 'published',
            'name'                   => json_encode(['fr' => 'Processus Canada — Résidence Permanente', 'en' => 'Canada Process — Permanent Residence']),
            'description'            => json_encode([
                'fr' => 'Procédure d\'immigration vers le Canada via le système Express Entry (résidence permanente).',
                'en' => 'Immigration procedure to Canada via the Express Entry system (permanent residence).',
            ]),
            'file_opening_fee'       => 0,
            'total_procedure_fees'   => 0,
            'estimated_duration_days'=> 365,
            'internal_notes'         => 'Procédure Express Entry. Pas d\'entretien employeur. WES obligatoire. Durée variable selon pool.',
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        // ----- SECTION 1 : Ouverture de dossier -----
        $s1 = $this->insertSection($flow, [
            'key'           => 'file_opening',
            'title'         => ['fr' => 'Ouverture de dossier', 'en' => 'File opening'],
            'description'   => ['fr' => 'Documents requis et frais d\'ouverture de dossier.', 'en' => 'Required documents and file opening fee.'],
            'section_order' => 1,
            'icon'          => 'ti-folder-open',
            'color'         => '#5B4FCF',
        ]);

        $this->insertSteps($flow, $s1, [
            [
                'step_type'         => 'DOCUMENT_COLLECTION',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => 'Dépôt des documents d\'ouverture', 'en' => 'Initial document submission'],
                'description'       => ['fr' => 'Le candidat soumet : CV mis à jour par JBIS, test TCF + équivalence si disponible, passeport légal en couleur, diplômes et attestations, preuves d\'expérience professionnelle.', 'en' => 'Candidate submits: CV updated by JBIS, TCF language test + equivalence certificate if available, legal passport in colour, certificates and attestations, work proofs.'],
                'requires_documents'        => true,
                'document_type_ids'         => $this->documentTypeIdsByCodes(['CV', 'PASSPORT', 'DIPLOMA', 'WORK_CERTIFICATE']),
                'is_blocking'       => true,
                'step_order'        => 1,
            ],
            [
                'step_type'         => 'PAYMENT',
                'payment_type'      => 'FILE_OPENING',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => 'Paiement frais d\'ouverture de dossier', 'en' => 'File opening fee payment'],
                'description'       => ['fr' => 'Versement obligatoire de 500 000 FCFA. Étape de présélection. Non remboursable.', 'en' => 'Mandatory payment of 500,000 CFA F. Pre-selection stage. Non-refundable.'],
                'default_amount'    => 500000,
                'is_blocking'       => true,
                'step_order'        => 2,
            ],
            [
                'step_type'         => 'SIGNING',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => 'Signature du contrat d\'engagement de procédure', 'en' => 'Procedure engagement contract signing'],
                'description'       => ['fr' => 'Le candidat signe le contrat d\'engagement de procédure avec JBIS.', 'en' => 'The candidate signs the procedure engagement contract with JBIS.'],
                'is_blocking'       => true,
                'step_order'        => 3,
            ],
        ]);

        // ----- SECTION 2 : Suivi de procédure -----
        $s2 = $this->insertSection($flow, [
            'key'           => 'procedure_followup',
            'title'         => ['fr' => 'Suivi de procédure', 'en' => 'Procedure follow-up'],
            'description'   => ['fr' => 'Évaluation du profil et premier versement.', 'en' => 'Profile evaluation and first instalment.'],
            'section_order' => 2,
            'icon'          => 'ti-clipboard-list',
            'color'         => '#0F6E56',
        ]);

        $this->insertSteps($flow, $s2, [
            [
                'step_type'         => 'SERVICE',
                'responsible_party' => 'JBIS',
                'title'             => ['fr' => 'Évaluation du profil candidat', 'en' => 'Candidate profile evaluation'],
                'description'       => ['fr' => 'JBIS évalue le profil du candidat pour vérifier son éligibilité au système Express Entry.', 'en' => 'JBIS evaluates the candidate profile to verify eligibility for the Express Entry system.'],
                'is_blocking'       => true,
                'step_order'        => 1,
            ],
            [
                'step_type'         => 'PAYMENT',
                'payment_type'      => 'PROCEDURE_INSTALMENT',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => '1er versement — démarrage procédure', 'en' => '1st instalment — procedure start'],
                'description'       => ['fr' => 'Versement de 1 500 000 FCFA dans une banque partenaire JBIS (ORIS FINANCE ou SCB) après approbation du profil.', 'en' => 'Payment of 1,500,000 CFA F at a JBIS partner bank (ORIS FINANCE or SCB) after profile approval.'],
                'default_amount'    => 1500000,
                'accepted_banks'    => ['ORIS_FINANCE', 'SCB'],
                'is_blocking'       => true,
                'step_order'        => 2,
            ],
        ]);

        // ----- SECTION 3 : Démarrage de la procédure -----
        $s3 = $this->insertSection($flow, [
            'key'           => 'procedure_start',
            'title'         => ['fr' => 'Démarrage de la procédure', 'en' => 'Procedure start'],
            'description'   => ['fr' => 'Démarches administratives Express Entry et préparation linguistique.', 'en' => 'Express Entry administrative steps and language preparation.'],
            'section_order' => 3,
            'icon'          => 'ti-rocket',
            'color'         => '#185FA5',
        ]);

        $this->insertSteps($flow, $s3, [
            [
                'step_type'         => 'SERVICE',
                'responsible_party' => 'SHARED',
                'title'             => ['fr' => 'Préparation au test de langue', 'en' => 'Language proficiency test preparation'],
                'description'       => ['fr' => 'Préparation aux tests de compétence linguistique en français et/ou anglais (TCF, IELTS, TEF, etc.).', 'en' => 'Preparation for language proficiency tests in French and/or English (TCF, IELTS, TEF, etc.).'],
                'is_blocking'       => false,
                'step_order'        => 1,
                'estimated_duration_days' => 60,
            ],
            [
                'step_type'         => 'ADMINISTRATIVE',
                'responsible_party' => 'SHARED',
                'title'             => ['fr' => 'Équivalence WES et soumission Express Entry', 'en' => 'WES equivalence and Express Entry profile submission'],
                'description'       => ['fr' => 'Obtention de l\'équivalence de diplômes par le WES (World Education Services) et soumission du profil Express Entry sur le portail IRCC.', 'en' => 'Obtaining degree equivalence through WES (World Education Services) and submitting the Express Entry profile on the IRCC portal.'],
                'is_blocking'       => true,
                'step_order'        => 2,
                'estimated_duration_days' => 90,
                'sla_alert_days'    => 120,
            ],
            [
                'step_type'         => 'ADMINISTRATIVE',
                'responsible_party' => 'AUTHORITY',
                'title'             => ['fr' => 'Réception de l\'invitation à déposer une demande', 'en' => 'Invitation to apply reception (ITA)'],
                'description'       => ['fr' => 'Immigration Canada envoie une invitation à présenter une demande de résidence permanente (ITA) aux candidats sélectionnés dans le pool Express Entry.', 'en' => 'Immigration Canada sends an Invitation To Apply (ITA) for permanent residence to candidates selected from the Express Entry pool.'],
                'is_blocking'       => true,
                'step_order'        => 3,
                'sla_alert_days'    => 30,
            ],
            [
                'step_type'         => 'ADMINISTRATIVE',
                'responsible_party' => 'SHARED',
                'title'             => ['fr' => 'Dépôt de la demande de résidence permanente', 'en' => 'Permanent residence application submission'],
                'description'       => ['fr' => 'Dépôt de la demande complète de résidence permanente auprès d\'Immigration Canada après réception de l\'invitation.', 'en' => 'Submission of the complete permanent residence application to Immigration Canada after receiving the ITA.'],
                'is_blocking'       => true,
                'step_order'        => 4,
                'estimated_duration_days' => 30,
            ],
        ]);

        // ----- SECTION 4 : Documents complémentaires après invitation -----
        $s4 = $this->insertSection($flow, [
            'key'                       => 'post_invitation_docs',
            'title'                     => ['fr' => 'Documents complémentaires après invitation', 'en' => 'Supplementary documents after invitation'],
            'description'               => ['fr' => 'Documents à soumettre au dossier après réception de l\'invitation.', 'en' => 'Documents to submit to the file after receiving the invitation.'],
            'section_order'             => 4,
            'icon'                      => 'ti-file-plus',
            'color'                     => '#993C1D',
            'visible_after_section_key' => 'procedure_start',
        ]);

        $this->insertSteps($flow, $s4, [
            [
                'step_type'                 => 'DOCUMENT_COLLECTION',
                'responsible_party'         => 'CANDIDATE',
                'title'                     => ['fr' => 'Dépôt des documents complémentaires', 'en' => 'Supplementary document submission'],
                'description'               => ['fr' => 'Le candidat soumet les documents requis après l\'invitation à déposer une demande.', 'en' => 'The candidate submits required documents after receiving the ITA.'],
                'requires_documents'        => true,
                'document_type_ids'         => $this->documentTypeIdsByCodes(['WORK_CERTIFICATE']),
                'is_blocking'               => true,
                'step_order'                => 1,
            ],
        ]);

        // ----- SECTION 5 : Versements finaux -----
        $s5 = $this->insertSection($flow, [
            'key'                       => 'final_payment',
            'title'                     => ['fr' => 'Versement final', 'en' => 'Final payment'],
            'description'               => ['fr' => 'Deuxième et dernier versement après sortie du pool Express Entry.', 'en' => 'Second and final instalment after exit from the Express Entry pool.'],
            'section_order'             => 5,
            'icon'                      => 'ti-credit-card',
            'color'                     => '#3B6D11',
            'visible_after_section_key' => 'post_invitation_docs',
        ]);

        $this->insertSteps($flow, $s5, [
            [
                'step_type'         => 'PAYMENT',
                'payment_type'      => 'PROCEDURE_INSTALMENT',
                'responsible_party' => 'CANDIDATE',
                'title'             => ['fr' => '2ème versement — sortie du pool', 'en' => '2nd instalment — pool exit'],
                'description'       => ['fr' => 'Versement de 1 500 000 FCFA après sortie du pool Express Entry et invitation reçue. Montant à déposer en banque partenaire JBIS.', 'en' => 'Payment of 1,500,000 CFA F after exit from the Express Entry pool and invitation received. To be deposited at a JBIS partner bank.'],
                'default_amount'    => 1500000,
                'accepted_banks'    => ['ORIS_FINANCE', 'SCB'],
                'is_blocking'       => true,
                'step_order'        => 1,
            ],
        ]);

        $this->recalculateFlowFees($flow);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param  list<string>  $codes
     * @return list<int>
     */
    private function documentTypeIdsByCodes(array $codes): array
    {
        if ($codes === []) {
            return [];
        }

        return DB::table('document_types')
            ->whereIn('code', $codes)
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function recalculateFlowFees(int $flowId): void
    {
        $flow = ProcessFlow::query()->find($flowId);
        if ($flow === null) {
            return;
        }

        app(ProcessFlowFeeRecalculator::class)->recalculate($flow);
    }

    private function insertSection(int $flowId, array $data): int
    {
        return DB::table('process_flow_sections')->insertGetId(array_merge([
            'process_flow_id'           => $flowId,
            'visible_after_section_key' => null,
            'color'                     => null,
            'icon'                      => null,
            'created_at'                => now(),
            'updated_at'                => now(),
        ], [
            'key'           => $data['key'],
            'title'         => json_encode($data['title']),
            'description'   => json_encode($data['description'] ?? null),
            'section_order' => $data['section_order'],
            'color'         => $data['color'] ?? null,
            'icon'          => $data['icon'] ?? null,
            'visible_after_section_key' => $data['visible_after_section_key'] ?? null,
        ]));
    }

    private function insertSteps(int $flowId, int $sectionId, array $steps): void
    {
        foreach ($steps as $step) {
            DB::table('process_steps')->insert(array_merge([
                'process_flow_id'              => $flowId,
                'process_flow_section_id'      => $sectionId,
                'payment_type'                 => null,
                'accepted_banks'               => null,
                'requires_documents'           => false,
                'document_type_ids'            => null,
                'description'                  => null,
                'internal_note'                => null,
                'is_blocking'                  => true,
                'is_required'                  => true,
                'default_amount'               => 0,
                'estimated_duration_days'      => null,
                'sla_alert_days'               => null,
                'created_at'                   => now(),
                'updated_at'                   => now(),
            ], array_filter([
                'step_type'                    => $step['step_type'],
                'payment_type'                 => $step['payment_type'] ?? null,
                'responsible_party'            => $step['responsible_party'],
                'title'                        => json_encode($step['title']),
                'description'                  => isset($step['description']) ? json_encode($step['description']) : null,
                'internal_note'                => $step['internal_note'] ?? null,
                'step_order'                   => $step['step_order'],
                'is_blocking'                  => $step['is_blocking'] ?? true,
                'is_required'                  => $step['is_required'] ?? true,
                'default_amount'               => $step['default_amount'] ?? 0,
                'accepted_banks'               => isset($step['accepted_banks']) ? json_encode($step['accepted_banks']) : null,
                'requires_documents'           => $step['requires_documents'] ?? false,
                'document_type_ids'            => isset($step['document_type_ids']) && $step['document_type_ids'] !== []
                    ? json_encode($step['document_type_ids'])
                    : null,
                'estimated_duration_days'      => $step['estimated_duration_days'] ?? null,
                'sla_alert_days'               => $step['sla_alert_days'] ?? null,
            ], fn($v) => $v !== null)));
        }
    }
};