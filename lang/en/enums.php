<?php

declare(strict_types=1);

return [
    'profile_type' => [
        'student' => [
            'label' => 'Student',
            'description' => 'Currently studying (school, university, or training center).',
        ],
        'recent_graduate' => [
            'label' => 'Recent graduate',
            'description' => 'Graduated less than 2 years ago.',
        ],
        'active_worker' => [
            'label' => 'Active worker',
            'description' => 'Currently employed or professionally active.',
        ],
        'job_seeker' => [
            'label' => 'Job seeker',
            'description' => 'Available for a new position.',
        ],
        'exploring' => [
            'label' => 'Discovering JBIS',
            'description' => 'Exploring opportunities before committing.',
        ],
    ],
    'civility' => [
        'mr' => [
            'label' => 'Mr',
        ],
        'mrs' => [
            'label' => 'Mrs',
        ],
        'miss' => [
            'label' => 'Miss',
        ],
    ],
    'career_intent' => [
        'work_abroad' => [
            'label' => 'Work abroad',
            'description' => 'Work visa, international placement, opportunities outside Cameroon.',
        ],
        'work_local' => [
            'label' => 'Work in Cameroon',
            'description' => 'Local offers, internships, and jobs within the country.',
        ],
        'visa_support' => [
            'label' => 'Visa & mobility support',
            'description' => 'Studies, residency, and administrative procedures with JBIS.',
        ],
        'explore' => [
            'label' => 'Discovering JBIS',
            'description' => 'Explore offers and services before committing.',
        ],
    ],
    'application_status' => [
        'PENDING' => [
            'label' => 'Pending',
        ],
        'IN_PROGRESS' => [
            'label' => 'In progress',
        ],
        'APPROVED' => [
            'label' => 'Approved',
        ],
        'REJECTED' => [
            'label' => 'Rejected',
        ],
        'CANCELLED' => [
            'label' => 'Cancelled',
        ],
    ],
    'language_course_status' => [
        'PLANNED' => [
            'label' => 'Planned',
        ],
        'IN_PROGRESS' => [
            'label' => 'In progress',
        ],
        'COMPLETED' => [
            'label' => 'Completed',
        ],
        'CANCELLED' => [
            'label' => 'Cancelled',
        ],
        'DEFERRED' => [
            'label' => 'Deferred',
        ],
        'FAILED' => [
            'label' => 'Failed',
        ],
    ],
];
