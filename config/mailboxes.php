<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Domaine messagerie JBIS (Google Workspace)
    |--------------------------------------------------------------------------
    |
    | Adresses secondaires configurées comme alias « Envoyer en tant que »
    | sur le compte SMTP (MAIL_USERNAME) dans Google Workspace Admin.
    |
    */

    'domain' => env('JBIS_MAIL_DOMAIN', 'jbis.cm'),

    'addresses' => [
        'contact' => [
            'address' => env('JBIS_MAIL_CONTACT', 'contact@jbis.cm'),
            'name' => env('JBIS_MAIL_CONTACT_NAME', 'JBIS — Contact'),
            'public' => true,
        ],
        'info' => [
            'address' => env('JBIS_MAIL_INFO', 'info@jbis.cm'),
            'name' => env('JBIS_MAIL_INFO_NAME', 'JBIS — Informations'),
            'public' => true,
        ],
        'noreply' => [
            'address' => env('JBIS_MAIL_NOREPLY', 'no-reply@jbis.cm'),
            'name' => env('JBIS_MAIL_NOREPLY_NAME', 'MyJob Best'),
            'public' => false,
        ],
        'dpo' => [
            'address' => env('JBIS_MAIL_DPO', 'dpo@jbis.cm'),
            'name' => env('JBIS_MAIL_DPO_NAME', 'JBIS — DPO'),
            'public' => true,
        ],
        'yaounde' => [
            'address' => env('JBIS_MAIL_YAOUNDE', 'yaounde@jbis.cm'),
            'name' => env('JBIS_MAIL_YAOUNDE_NAME', 'JBIS Yaoundé'),
            'public' => true,
        ],
        'douala' => [
            'address' => env('JBIS_MAIL_DOUALA', 'douala@jbis.cm'),
            'name' => env('JBIS_MAIL_DOUALA_NAME', 'JBIS Douala'),
            'public' => true,
        ],
        'staff' => [
            'address' => env('JBIS_MAIL_STAFF', 'staff@jbis.cm'),
            'name' => env('JBIS_MAIL_STAFF_NAME', 'JBIS — Équipe'),
            'public' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routage applicatif (clé → clé d'adresse ci-dessus)
    |--------------------------------------------------------------------------
    */

    'routing' => [
        'transactional' => 'noreply',
        'reply_to_default' => 'contact',
        'newsletter' => 'noreply',
        'legal' => 'noreply',
        'welcome' => 'noreply',
        'recruiter_notify' => env('JBIS_MAIL_RECRUITER_NOTIFY_KEY', 'contact'),
        'campaign_reply_to' => 'contact',
    ],

];
