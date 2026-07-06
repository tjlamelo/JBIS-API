<?php

declare(strict_types=1);
use App\Core\Domain\Shared\Ai\Services\FakeLanguageModelClient;
use App\Core\Domain\Shared\Ai\Services\GeminiLanguageModelClient;
use App\Core\Domain\Shared\Ai\Services\GroqLanguageModelClient;

return [
    /**
     * Pilote l'implémentation exposée via `LanguageModelClientInterface`.
     * Exemples : gemini, groq, fake (tests / hors-ligne).
     */
    'driver' => env('AI_DRIVER', 'gemini'),

    'providers' => [
        'gemini' => GeminiLanguageModelClient::class,
        'groq' => GroqLanguageModelClient::class,
        /** @deprecated Utiliser le driver `groq` (alias conservé pour compatibilité). */
        'grok' => GroqLanguageModelClient::class,
        'fake' => FakeLanguageModelClient::class,
    ],

    'gemini' => [
        'api_key' => env('AI_GEMINI_API_KEY', ''),
        'model' => env('AI_GEMINI_MODEL', 'gemini-2.0-flash'),
        'base_url' => rtrim((string) env('AI_GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/'),
        'timeout' => (int) env('AI_GEMINI_TIMEOUT', 60),
    ],

    'groq' => [
        'api_key' => env('AI_GROQ_API_KEY', env('AI_GROK_API_KEY', '')),
        'model' => env('AI_GROQ_MODEL', env('AI_GROK_MODEL', 'llama-3.3-70b-versatile')),
        'vision_model' => env('AI_GROQ_VISION_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
        'base_url' => rtrim((string) env('AI_GROQ_BASE_URL', env('AI_GROK_BASE_URL', 'https://api.groq.com/openai/v1')), '/'),
        'timeout' => (int) env('AI_GROQ_TIMEOUT', env('AI_GROK_TIMEOUT', 60)),
    ],

    'document_extraction' => [
        /** Active ou désactive toute extraction IA à l'upload de documents. */
        'enabled' => (bool) env('AI_DOCUMENT_EXTRACTION_ENABLED', true),
        /** Pilote IA dédié (recommandé : groq). */
        'driver' => env('AI_DOCUMENT_EXTRACTION_DRIVER', 'groq'),
        /** Entrée vision : base64 (local + prod) ou url (assets publics uniquement). */
        'vision_input' => env('AI_DOCUMENT_EXTRACTION_VISION_INPUT', 'base64'),
        'pdf' => [
            'enabled' => (bool) env('AI_DOCUMENT_EXTRACTION_PDF_ENABLED', true),
            'max_pages' => (int) env('AI_DOCUMENT_EXTRACTION_PDF_MAX_PAGES', 2),
            'min_text_chars' => (int) env('AI_DOCUMENT_EXTRACTION_PDF_MIN_TEXT_CHARS', 200),
        ],
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
