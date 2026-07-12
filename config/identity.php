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

];
