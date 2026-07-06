<?php

declare(strict_types=1);

return [
    'profile_type' => [
        'student' => [
            'label' => 'Étudiant(e)',
            'description' => 'En cours de formation (école, université, centre de formation).',
        ],
        'recent_graduate' => [
            'label' => 'Jeune diplômé(e)',
            'description' => 'Formation terminée depuis moins de 2 ans.',
        ],
        'active_worker' => [
            'label' => 'Travailleur actif',
            'description' => 'En poste ou en activité professionnelle.',
        ],
        'job_seeker' => [
            'label' => 'En recherche d\'emploi',
            'description' => 'Disponible pour un nouveau poste.',
        ],
        'exploring' => [
            'label' => 'Je découvre JBIS',
            'description' => 'Explorer les opportunités avant de me projeter.',
        ],
    ],
    'career_intent' => [
        'work_abroad' => [
            'label' => 'Travailler à l\'étranger',
            'description' => 'Visa travail, placement international, opportunités hors Cameroun.',
        ],
        'work_local' => [
            'label' => 'Travailler au Cameroun',
            'description' => 'Offres locales, stages et emplois au sein du pays.',
        ],
        'visa_support' => [
            'label' => 'Accompagnement visa & mobilité',
            'description' => 'Études, résidence, démarches administratives avec JBIS.',
        ],
        'explore' => [
            'label' => 'Je découvre JBIS',
            'description' => 'Explorer les offres et services avant de me projeter.',
        ],
    ],
    'application_status' => [
        'PENDING' => [
            'label' => 'En attente',
        ],
        'IN_PROGRESS' => [
            'label' => 'En cours',
        ],
        'APPROVED' => [
            'label' => 'Approuvée',
        ],
        'REJECTED' => [
            'label' => 'Rejetée',
        ],
        'CANCELLED' => [
            'label' => 'Annulée',
        ],
    ],
    'language_course_status' => [
        'PLANNED' => [
            'label' => 'Prévu',
        ],
        'IN_PROGRESS' => [
            'label' => 'En cours',
        ],
        'COMPLETED' => [
            'label' => 'Terminé',
        ],
        'CANCELLED' => [
            'label' => 'Annulé',
        ],
        'DEFERRED' => [
            'label' => 'Reporté',
        ],
        'FAILED' => [
            'label' => 'Échoué',
        ],
    ],
];
