<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Catalog\Models\Agency;
use App\Core\Domain\Catalog\Models\EducationLevel;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\Skill;
use App\Core\Domain\Catalog\Models\SkillCategory;
use App\Core\Domain\Catalog\Models\Training;
use App\Core\Domain\Communication\Models\DiscoverySource;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Location\Models\Language;
use App\Core\Domain\Location\Models\LanguageLevel;
use Spatie\Permission\Models\Role;

/**
 * Schéma des critères de recherche avancée (métadonnées + options de listes).
 */
final class AdminUserSearchFilterSchema
{
    /**
     * @return list<array<string, mixed>>
     */
    public function filterDefinitions(): array
    {
        return [
            // —— Recherche globale ——
            [
                'key' => 'search',
                'type' => 'text',
                'label' => 'Recherche globale',
                'description' => 'Nom, email, téléphone, prénom, nom, matricule',
                'group' => 'general',
            ],

            // —— Compte ——
            [
                'key' => 'role',
                'type' => 'select',
                'label' => 'Rôle',
                'group' => 'compte',
                'options_source' => 'roles',
            ],
            [
                'key' => 'active',
                'type' => 'tristate',
                'label' => 'Compte actif',
                'group' => 'compte',
                'options' => self::tristateOptions('Actifs', 'Inactifs'),
            ],
            [
                'key' => 'verified',
                'type' => 'tristate',
                'label' => 'Email vérifié',
                'group' => 'compte',
                'options' => self::tristateOptions('Vérifiés', 'Non vérifiés'),
            ],

            // —— Identité & démographie ——
            [
                'key' => 'profile_exists',
                'type' => 'tristate',
                'label' => 'Profil créé',
                'group' => 'identite',
                'options' => self::tristateYesNo(),
            ],
            [
                'key' => 'profile_approved',
                'type' => 'tristate',
                'label' => 'Profil approuvé',
                'group' => 'identite',
                'options' => [
                    ['value' => '', 'label' => 'Tous'],
                    ['value' => '1', 'label' => 'Approuvé'],
                    ['value' => '0', 'label' => 'En attente'],
                ],
            ],
            [
                'key' => 'gender',
                'type' => 'select',
                'label' => 'Genre',
                'group' => 'identite',
                'options' => [
                    ['value' => 'M', 'label' => 'Homme'],
                    ['value' => 'F', 'label' => 'Femme'],
                ],
            ],
            [
                'key' => 'min_age',
                'type' => 'number',
                'label' => 'Âge minimum',
                'description' => 'Années révolues',
                'group' => 'identite',
            ],
            [
                'key' => 'max_age',
                'type' => 'number',
                'label' => 'Âge maximum',
                'description' => 'Années révolues',
                'group' => 'identite',
            ],
            [
                'key' => 'birth_after',
                'type' => 'date',
                'label' => 'Né(e) après le',
                'group' => 'identite',
            ],
            [
                'key' => 'birth_before',
                'type' => 'date',
                'label' => 'Né(e) avant le',
                'group' => 'identite',
            ],
            [
                'key' => 'marital_status',
                'type' => 'select',
                'label' => 'Situation matrimoniale',
                'group' => 'identite',
                'options' => [
                    ['value' => 'SINGLE', 'label' => 'Célibataire'],
                    ['value' => 'MARRIED', 'label' => 'Marié(e)'],
                    ['value' => 'DIVORCED', 'label' => 'Divorcé(e)'],
                    ['value' => 'WIDOWED', 'label' => 'Veuf(ve)'],
                ],
            ],
            [
                'key' => 'min_children',
                'type' => 'number',
                'label' => 'Enfants (min.)',
                'group' => 'identite',
            ],
            [
                'key' => 'max_children',
                'type' => 'number',
                'label' => 'Enfants (max.)',
                'group' => 'identite',
            ],
            [
                'key' => 'nationality_country_id',
                'type' => 'select',
                'label' => 'Nationalité',
                'group' => 'identite',
                'options_source' => 'countries',
            ],
            [
                'key' => 'residence_city',
                'type' => 'text',
                'label' => 'Ville de résidence',
                'group' => 'identite',
            ],
            [
                'key' => 'agency_id',
                'type' => 'select',
                'label' => 'Agence JBIS',
                'group' => 'identite',
                'options_source' => 'agencies',
            ],
            [
                'key' => 'discovery_source_id',
                'type' => 'select',
                'label' => 'Source de découverte',
                'group' => 'identite',
                'options_source' => 'discovery_sources',
            ],
            [
                'key' => 'has_matricule',
                'type' => 'tristate',
                'label' => 'Matricule renseigné',
                'group' => 'identite',
                'options' => self::tristateYesNo(),
            ],

            // —— Domaines & secteurs ——
            [
                'key' => 'sector_ids',
                'type' => 'multiselect',
                'label' => 'Domaines / secteurs ciblés',
                'description' => 'Secteurs d\'activité visés par le candidat',
                'group' => 'domaines',
                'options_source' => 'sectors',
            ],
            [
                'key' => 'sector_match',
                'type' => 'select',
                'label' => 'Correspondance secteurs',
                'group' => 'domaines',
                'options' => [
                    ['value' => 'any', 'label' => 'Au moins un secteur'],
                    ['value' => 'all', 'label' => 'Tous les secteurs sélectionnés'],
                ],
            ],
            [
                'key' => 'experience_industry_ids',
                'type' => 'multiselect',
                'label' => 'Secteurs d\'expérience professionnelle',
                'description' => 'Domaines des postes occupés',
                'group' => 'domaines',
                'options_source' => 'sectors',
            ],
            [
                'key' => 'experience_job_title',
                'type' => 'text',
                'label' => 'Intitulé de poste',
                'description' => 'Recherche dans les expériences',
                'group' => 'domaines',
            ],
            [
                'key' => 'experience_company',
                'type' => 'text',
                'label' => 'Entreprise',
                'description' => 'Nom d\'employeur dans les expériences',
                'group' => 'domaines',
            ],

            // —— Parcours professionnel ——
            [
                'key' => 'min_years_experience',
                'type' => 'number',
                'label' => 'Expérience totale (min. années)',
                'group' => 'parcours',
            ],
            [
                'key' => 'max_years_experience',
                'type' => 'number',
                'label' => 'Expérience totale (max. années)',
                'group' => 'parcours',
            ],
            [
                'key' => 'min_experiences',
                'type' => 'number',
                'label' => 'Nb min. expériences',
                'group' => 'parcours',
            ],
            [
                'key' => 'max_experiences',
                'type' => 'number',
                'label' => 'Nb max. expériences',
                'group' => 'parcours',
            ],
            [
                'key' => 'experience_country_id',
                'type' => 'select',
                'label' => 'Pays d\'expérience',
                'group' => 'parcours',
                'options_source' => 'countries',
            ],
            [
                'key' => 'experience_status',
                'type' => 'select',
                'label' => 'Statut validation expérience',
                'group' => 'parcours',
                'options' => self::validationStatusOptions(),
            ],
            [
                'key' => 'has_current_job',
                'type' => 'tristate',
                'label' => 'Poste actuel en cours',
                'group' => 'parcours',
                'options' => self::tristateYesNo(),
            ],

            // —— Formation & diplômes ——
            [
                'key' => 'education_level_id',
                'type' => 'select',
                'label' => 'Niveau de formation',
                'group' => 'formation',
                'options_source' => 'education_levels',
            ],
            [
                'key' => 'education_field',
                'type' => 'text',
                'label' => 'Domaine d\'études',
                'description' => 'Filière, spécialité',
                'group' => 'formation',
            ],
            [
                'key' => 'education_country_id',
                'type' => 'select',
                'label' => 'Pays de formation',
                'group' => 'formation',
                'options_source' => 'countries',
            ],
            [
                'key' => 'min_educations',
                'type' => 'number',
                'label' => 'Nb min. formations',
                'group' => 'formation',
            ],
            [
                'key' => 'max_educations',
                'type' => 'number',
                'label' => 'Nb max. formations',
                'group' => 'formation',
            ],
            [
                'key' => 'education_approved',
                'type' => 'tristate',
                'label' => 'Formation approuvée',
                'group' => 'formation',
                'options' => self::tristateYesNo(),
            ],
            [
                'key' => 'min_certifications',
                'type' => 'number',
                'label' => 'Nb min. certifications',
                'group' => 'formation',
            ],
            [
                'key' => 'certification_name',
                'type' => 'text',
                'label' => 'Certification',
                'description' => 'Nom ou organisme émetteur',
                'group' => 'formation',
            ],
            [
                'key' => 'certification_status',
                'type' => 'select',
                'label' => 'Statut certification',
                'group' => 'formation',
                'options' => self::validationStatusOptions(),
            ],

            // —— Compétences & langues ——
            [
                'key' => 'language_id',
                'type' => 'select',
                'label' => 'Langue parlée',
                'group' => 'competences',
                'options_source' => 'languages',
            ],
            [
                'key' => 'language_level_id',
                'type' => 'select',
                'label' => 'Niveau de langue (min.)',
                'group' => 'competences',
                'options_source' => 'language_levels',
            ],
            [
                'key' => 'language_approved',
                'type' => 'tristate',
                'label' => 'Langue validée',
                'group' => 'competences',
                'options' => self::tristateYesNo(),
            ],
            [
                'key' => 'skill_ids',
                'type' => 'multiselect',
                'label' => 'Compétences',
                'group' => 'competences',
                'options_source' => 'skills',
            ],
            [
                'key' => 'skill_category_id',
                'type' => 'select',
                'label' => 'Catégorie de compétence',
                'group' => 'competences',
                'options_source' => 'skill_categories',
            ],
            [
                'key' => 'skill_level',
                'type' => 'select',
                'label' => 'Niveau de compétence',
                'group' => 'competences',
                'options' => [
                    ['value' => 'BEGINNER', 'label' => 'Débutant'],
                    ['value' => 'INTERMEDIATE', 'label' => 'Intermédiaire'],
                    ['value' => 'ADVANCED', 'label' => 'Avancé'],
                    ['value' => 'EXPERT', 'label' => 'Expert'],
                ],
            ],
            [
                'key' => 'min_skill_years',
                'type' => 'number',
                'label' => 'Années d\'expérience sur la compétence (min.)',
                'group' => 'competences',
            ],

            // —— Mobilité & visas ——
            [
                'key' => 'preferred_country_ids',
                'type' => 'multiselect',
                'label' => 'Pays cibles (mobilité)',
                'group' => 'mobilite',
                'options_source' => 'countries',
            ],
            [
                'key' => 'visa_country_id',
                'type' => 'select',
                'label' => 'Historique visa — pays',
                'group' => 'mobilite',
                'options_source' => 'countries',
            ],
            [
                'key' => 'visa_status',
                'type' => 'select',
                'label' => 'Statut visa',
                'group' => 'mobilite',
                'options' => [
                    ['value' => 'GRANTED', 'label' => 'Accordé'],
                    ['value' => 'REFUSED', 'label' => 'Refusé'],
                    ['value' => 'EXPIRED', 'label' => 'Expiré'],
                    ['value' => 'CANCELLED', 'label' => 'Annulé'],
                ],
            ],
            [
                'key' => 'has_visa_history',
                'type' => 'tristate',
                'label' => 'Historique visa renseigné',
                'group' => 'mobilite',
                'options' => self::tristateYesNo(),
            ],

            // —— Documents ——
            [
                'key' => 'document_type_id',
                'type' => 'select',
                'label' => 'Type de document',
                'group' => 'documents',
                'options_source' => 'document_types',
            ],
            [
                'key' => 'document_status',
                'type' => 'select',
                'label' => 'Statut document',
                'group' => 'documents',
                'options' => [
                    ['value' => 'PENDING', 'label' => 'En attente'],
                    ['value' => 'APPROVED', 'label' => 'Approuvé'],
                    ['value' => 'REJECTED', 'label' => 'Refusé'],
                    ['value' => 'EXPIRED', 'label' => 'Expiré'],
                ],
            ],
            [
                'key' => 'min_documents',
                'type' => 'number',
                'label' => 'Nb min. documents',
                'group' => 'documents',
            ],
            [
                'key' => 'max_documents',
                'type' => 'number',
                'label' => 'Nb max. documents',
                'group' => 'documents',
            ],
            [
                'key' => 'has_valid_documents',
                'type' => 'tristate',
                'label' => 'Documents valides (non expirés)',
                'group' => 'documents',
                'options' => self::tristateYesNo(),
            ],

            // —— Candidatures ——
            [
                'key' => 'has_applications',
                'type' => 'tristate',
                'label' => 'A des candidatures',
                'group' => 'candidatures',
                'options' => self::tristateYesNo(),
            ],
            [
                'key' => 'min_applications',
                'type' => 'number',
                'label' => 'Nb min. candidatures',
                'group' => 'candidatures',
            ],
            [
                'key' => 'application_status',
                'type' => 'multiselect',
                'label' => 'Statut candidature',
                'group' => 'candidatures',
                'options' => [
                    ['value' => 'PENDING', 'label' => 'En attente'],
                    ['value' => 'IN_PROGRESS', 'label' => 'En cours'],
                    ['value' => 'APPROVED', 'label' => 'Approuvée'],
                    ['value' => 'REJECTED', 'label' => 'Refusée'],
                    ['value' => 'CANCELLED', 'label' => 'Annulée'],
                ],
            ],
            [
                'key' => 'category_id',
                'type' => 'select',
                'label' => 'Secteur de l\'offre candidatée',
                'group' => 'candidatures',
                'options_source' => 'sectors',
            ],

            // —— Entretiens ——
            [
                'key' => 'interview_status',
                'type' => 'select',
                'label' => 'Statut entretien',
                'group' => 'entretiens',
                'options' => [
                    ['value' => 'SCHEDULED', 'label' => 'Planifié'],
                    ['value' => 'COMPLETED', 'label' => 'Terminé'],
                    ['value' => 'CANCELLED', 'label' => 'Annulé'],
                    ['value' => 'RESCHEDULED', 'label' => 'Reporté'],
                    ['value' => 'NO_SHOW', 'label' => 'Absent'],
                ],
            ],
            [
                'key' => 'interview_result',
                'type' => 'select',
                'label' => 'Résultat entretien',
                'group' => 'entretiens',
                'options' => [
                    ['value' => 'PASSED', 'label' => 'Réussi'],
                    ['value' => 'FAILED', 'label' => 'Échoué'],
                    ['value' => 'PENDING', 'label' => 'En attente'],
                    ['value' => 'WAITING_LIST', 'label' => 'Liste d\'attente'],
                ],
            ],
            [
                'key' => 'has_interviews',
                'type' => 'tristate',
                'label' => 'A eu un entretien',
                'group' => 'entretiens',
                'options' => self::tristateYesNo(),
            ],

            // —— Formations JBIS & stages ——
            [
                'key' => 'training_id',
                'type' => 'select',
                'label' => 'Formation JBIS suivie',
                'group' => 'formations_jbis',
                'options_source' => 'trainings',
            ],
            [
                'key' => 'training_status',
                'type' => 'select',
                'label' => 'Statut formation JBIS',
                'group' => 'formations_jbis',
                'options' => [
                    ['value' => 'ONGOING', 'label' => 'En cours'],
                    ['value' => 'COMPLETED', 'label' => 'Terminée'],
                    ['value' => 'CANCELED', 'label' => 'Annulée'],
                ],
            ],
            [
                'key' => 'has_trainings',
                'type' => 'tristate',
                'label' => 'Formation JBIS suivie',
                'group' => 'formations_jbis',
                'options' => self::tristateYesNo(),
            ],
            [
                'key' => 'has_internships',
                'type' => 'tristate',
                'label' => 'A des stages',
                'group' => 'formations_jbis',
                'options' => self::tristateYesNo(),
            ],
            [
                'key' => 'internship_type',
                'type' => 'select',
                'label' => 'Type de stage',
                'group' => 'formations_jbis',
                'options' => [
                    ['value' => 'ACADEMIC', 'label' => 'Académique'],
                    ['value' => 'PROFESSIONAL', 'label' => 'Professionnel'],
                    ['value' => 'OTHER', 'label' => 'Autre'],
                ],
            ],

            // —— Dates ——
            [
                'key' => 'created_after',
                'type' => 'date',
                'label' => 'Inscrit après le',
                'group' => 'dates',
            ],
            [
                'key' => 'created_before',
                'type' => 'date',
                'label' => 'Inscrit avant le',
                'group' => 'dates',
            ],
            [
                'key' => 'updated_after',
                'type' => 'date',
                'label' => 'Mis à jour après le',
                'group' => 'dates',
            ],
            [
                'key' => 'updated_before',
                'type' => 'date',
                'label' => 'Mis à jour avant le',
                'group' => 'dates',
            ],
        ];
    }

    /**
     * @return array<string, list<array{value: string, label: string}>>
     */
    public function optionLists(string $locale): array
    {
        return [
            'roles' => Role::query()->orderBy('name')->pluck('name')->map(fn (string $name) => [
                'value' => $name,
                'label' => $name,
            ])->values()->all(),
            'countries' => Country::query()->where('is_active', true)->orderBy('name->'.$locale)->get()->map(fn (Country $c) => [
                'value' => (string) $c->id,
                'label' => $c->getTranslation('name', $locale),
            ])->values()->all(),
            'cities' => City::query()
                ->orderBy('name->'.$locale)
                ->limit(300)
                ->get()
                ->map(fn ($c) => [
                    'value' => (string) $c->id,
                    'label' => $c->getTranslation('name', $locale),
                ])->values()->all(),
            'agencies' => Agency::query()->orderBy('name->'.$locale)->get()->map(fn (Agency $a) => [
                'value' => (string) $a->id,
                'label' => $a->getTranslation('name', $locale),
            ])->values()->all(),
            'sectors' => Category::query()->orderBy('name->'.$locale)->get()->map(fn (Category $s) => [
                'value' => (string) $s->id,
                'label' => $s->getTranslation('name', $locale),
            ])->values()->all(),
            'languages' => Language::query()->orderBy('name->'.$locale)->get()->map(fn (Language $l) => [
                'value' => (string) $l->id,
                'label' => $l->getTranslation('name', $locale),
            ])->values()->all(),
            'language_levels' => LanguageLevel::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (LanguageLevel $l) => [
                    'value' => (string) $l->id,
                    'label' => $l->getTranslation('name', $locale),
                ])->values()->all(),
            'skills' => Skill::query()->orderBy('name->'.$locale)->limit(500)->get()->map(fn (Skill $s) => [
                'value' => (string) $s->id,
                'label' => $s->getTranslation('name', $locale),
            ])->values()->all(),
            'skill_categories' => SkillCategory::query()->orderBy('name->'.$locale)->get()->map(fn (SkillCategory $c) => [
                'value' => (string) $c->id,
                'label' => $c->getTranslation('name', $locale),
            ])->values()->all(),
            'education_levels' => EducationLevel::query()->orderBy('name->'.$locale)->get()->map(fn (EducationLevel $l) => [
                'value' => (string) $l->id,
                'label' => $l->getTranslation('name', $locale),
            ])->values()->all(),
            'document_types' => DocumentType::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (DocumentType $t) => [
                    'value' => (string) $t->id,
                    'label' => $t->resolvedLabel($locale),
                ])->values()->all(),
            'discovery_sources' => DiscoverySource::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (DiscoverySource $s) => [
                    'value' => (string) $s->id,
                    'label' => $s->label,
                ])->values()->all(),
            'trainings' => Training::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->limit(200)
                ->get()
                ->map(fn (Training $t) => [
                    'value' => (string) $t->id,
                    'label' => $t->title,
                ])->values()->all(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function sortOptions(): array
    {
        return [
            ['value' => 'created_at', 'label' => 'Date d\'inscription'],
            ['value' => 'updated_at', 'label' => 'Dernière mise à jour'],
            ['value' => 'name', 'label' => 'Nom'],
            ['value' => 'email', 'label' => 'Email'],
            ['value' => 'date_of_birth', 'label' => 'Date de naissance'],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private static function tristateOptions(string $yesLabel, string $noLabel): array
    {
        return [
            ['value' => '', 'label' => 'Tous'],
            ['value' => '1', 'label' => $yesLabel],
            ['value' => '0', 'label' => $noLabel],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private static function tristateYesNo(): array
    {
        return self::tristateOptions('Oui', 'Non');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private static function validationStatusOptions(): array
    {
        return [
            ['value' => 'PENDING', 'label' => 'En attente'],
            ['value' => 'APPROVED', 'label' => 'Approuvé'],
            ['value' => 'REJECTED', 'label' => 'Refusé'],
        ];
    }
}
