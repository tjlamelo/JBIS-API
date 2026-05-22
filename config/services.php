<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ga4' => [
        'property_id' => env('GA4_PROPERTY_ID'),
        'service_account_json' => env('GA4_SERVICE_ACCOUNT_JSON'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'queensms' => [
        'base_url' => env('QUEENSMS_BASE_URL'),
        'api_key' => env('QUEENSMS_API_KEY'),
        'sender_id' => env('QUEENSMS_SENDER_ID'),
        'timeout' => (int) env('QUEENSMS_TIMEOUT', 10),
    ],

    'cpanel' => [
        'host' => env('CPANEL_DOMAIN'),
        'username' => env('CPANEL_USER'),
        'token' => env('CPANEL_TOKEN'),
        'primary_domain' => env('CPANEL_PRIMARY_DOMAIN'),
        'timeout' => (int) env('CPANEL_TIMEOUT', 15),
    ],

];
