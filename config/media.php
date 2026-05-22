<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary (CDN primaire)
    |--------------------------------------------------------------------------
    |
    | enabled  : interrupteur global. Quand `false`, le résolveur d'URL ignore
    |            totalement Cloudinary (utile pour maintenance, dépassement de
    |            quota, ou bascule forcée sur le miroir local).
    | prefix   : préfixe de dossier appliqué côté Cloudinary par le driver
    |            (cf. CloudinaryStorageDriver). C'est purement un rangement
    |            dans le dashboard Cloudinary.
    */
    'cloudinary' => [
        'enabled' => env('CLOUDINARY_ENABLED', true),
        'prefix' => env('CLOUDINARY_PREFIX', 'jbis.cm'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Miroir local (assets.jbis.cm)
    |--------------------------------------------------------------------------
    */
    'local' => [
        'disk' => 'jbis_assets',
        'base_url' => env('ASSETS_DOMAIN', 'https://assets.jbis.cm'),
    ],

];
