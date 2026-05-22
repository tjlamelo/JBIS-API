<?php

declare(strict_types=1);
use App\Core\Domain\Shared\Ai\Services\FakeLanguageModelClient;
use App\Core\Domain\Shared\Ai\Services\GeminiLanguageModelClient;

return [
    /**
     * Pilote l'implémentation exposée via `LanguageModelClientInterface`.
     * Exemples : gemini, fake (tests / hors-ligne).
     */
    'driver' => env('AI_DRIVER', 'gemini'),

    'providers' => [
        'gemini' => GeminiLanguageModelClient::class,
        'fake' => FakeLanguageModelClient::class,
    ],

    'gemini' => [
        'api_key' => env('AI_GEMINI_API_KEY', ''),
        'model' => env('AI_GEMINI_MODEL', 'gemini-2.0-flash'),
        'base_url' => rtrim((string) env('AI_GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/'),
        'timeout' => (int) env('AI_GEMINI_TIMEOUT', 60),
    ],

    'fake' => [
        'response' => env('AI_FAKE_RESPONSE', 'Réponse simulée (driver fake).'),
        /**
         * Réponse JSON simulée pour les workflows structurés (tests / démo hors Gemini).
         * Exemple minimal pour le brouillon profil : voir tests ou surcharge en config runtime.
         */
        'structured_stub' => [
            'notes' => '',
            'user_profile' => [
                'first_name' => '',
                'last_name' => '',
                'date_of_birth' => '',
                'place_of_birth' => '',
                'nationality_country_name' => '',
                'residence_city_name' => '',
                'address' => '',
                'phone_number2' => '',
                'phone_number3' => '',
                'gender' => '',
                'bio' => '',
                'marital_status' => '',
                'number_of_children' => 0,
                'email_institutional' => '',
            ],
            'educations' => [],
            'experiences' => [],
            'certifications' => [],
            'languages' => [],
            'formations' => [],
        ],
    ],
];
