<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API credentials
    |--------------------------------------------------------------------------
    |
    | Récupérables sur https://developer.ilovepdf.com (Projects).
    | La clé publique (project_public_...) et la clé secrète (secret_key_...)
    | sont nécessaires pour signer les requêtes auprès de l'API iLovePDF.
    |
    */
    'public_key' => env('ILOVEPDF_PUBLIC_KEY'),
    'secret_key' => env('ILOVEPDF_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Réglages réseau
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('ILOVEPDF_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Comportement par défaut des tâches
    |--------------------------------------------------------------------------
    |
    | - ignore_errors    : si un fichier échoue dans un batch, la tâche continue.
    | - ignore_password  : ignore les PDF protégés au lieu de stopper la tâche.
    | - try_pdf_repair   : tente de réparer un PDF corrompu avant traitement.
    |
    */
    'defaults' => [
        'ignore_errors' => true,
        'ignore_password' => true,
        'try_pdf_repair' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Compression
    |--------------------------------------------------------------------------
    |
    | Niveau par défaut pour la compression : low | recommended | extreme.
    |
    */
    'compression_level' => env('ILOVEPDF_COMPRESSION_LEVEL', 'recommended'),

    /*
    |--------------------------------------------------------------------------
    | Publication des documents traités
    |--------------------------------------------------------------------------
    |
    | Quand `PdfDocumentService` publie le résultat (compress, merge, watermark
    | etc.), il l'écrit sur ce disque, sous ce dossier. La sous-arborescence
    | YYYY/MM est ajoutée automatiquement.
    |
    | NB : la source de vérité pour le traitement est toujours un disque
    | filesystem (`jbis_assets`). Cloudinary n'intervient pas ici — il reste
    | un CDN d'affichage géré ailleurs.
    |
    */
    'documents' => [
        'disk' => env('ILOVEPDF_DOCUMENTS_DISK', 'jbis_assets'),
        'folder' => env('ILOVEPDF_DOCUMENTS_FOLDER', 'documents/processed'),
    ],

];
