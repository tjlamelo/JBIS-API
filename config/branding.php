<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Espace applicatif (produit)
    |--------------------------------------------------------------------------
    */
    'product_name' => env('BRAND_PRODUCT_NAME', 'MyJob Best'),

    /*
    |--------------------------------------------------------------------------
    | Raison sociale / société
    |--------------------------------------------------------------------------
    */
    'company_name' => env('BRAND_COMPANY_NAME', 'Job Best International Services'),

    /*
    |--------------------------------------------------------------------------
    | Logo e-mails (URL absolue). Vide = {APP_URL}/assets/img/logo-jbis.png
    |--------------------------------------------------------------------------
    */
    'logo_url' => env('BRAND_LOGO_URL'),

];
