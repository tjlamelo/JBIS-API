<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Mot de passe par défaut (création / import admin)
    |--------------------------------------------------------------------------
    |
    | Utilisé quand aucun mot de passe n'est fourni lors de la création rapide
    | ou de l'import Excel d'utilisateurs / candidats.
    |
    */
    'default_user_password' => env('JBIS_DEFAULT_USER_PASSWORD', 'Jbis2026!'),

    /*
    |--------------------------------------------------------------------------
    | Domaine e-mail fictif (création rapide sans e-mail)
    |--------------------------------------------------------------------------
    |
    | Utilisé pour générer prenom.nom@domaine quand aucun e-mail n'est fourni.
    | Ces adresses ne reçoivent pas de mails applicatifs.
    |
    */
    'placeholder_email_domain' => env('JBIS_PLACEHOLDER_EMAIL_DOMAIN', 'jbis.cm'),

];
