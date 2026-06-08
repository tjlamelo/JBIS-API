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
        'recruiter_base_domain' => env('CPANEL_RECRUITER_BASE_DOMAIN', env('CPANEL_PRIMARY_DOMAIN', 'jbis.cm')),
        'recruiter_portal_prefix' => env('CPANEL_RECRUITER_PORTAL_PREFIX', 'recruteur'),
        'recruiter_docroot' => env('CPANEL_RECRUITER_DOCROOT'),
    ],

    'recruiter' => [
        'onboarding_enabled' => (bool) env('RECRUITER_ONBOARDING_ENABLED', true),
        'notify_email' => env('RECRUITER_PORTAL_NOTIFY_EMAIL'),
        'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
        'auto_provision_on_approval' => (bool) env('RECRUITER_AUTO_PROVISION_ON_APPROVAL', false),
    ],

    'newsletter' => [
        'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
        'unsubscribe_url' => env('NEWSLETTER_UNSUBSCRIBE_URL', env('FRONTEND_URL', 'http://localhost:3000').'/newsletter/unsubscribe'),
        'cameroon_country_code' => env('NEWSLETTER_CAMEROON_COUNTRY_CODE', 'CM'),
        'max_offers_per_section' => (int) env('NEWSLETTER_MAX_OFFERS_PER_SECTION', 8),
        'offer_lookback_days' => (int) env('NEWSLETTER_OFFER_LOOKBACK_DAYS', 14),
        'schedule_enabled' => (bool) env('NEWSLETTER_SCHEDULE_ENABLED', true),
    ],

];
