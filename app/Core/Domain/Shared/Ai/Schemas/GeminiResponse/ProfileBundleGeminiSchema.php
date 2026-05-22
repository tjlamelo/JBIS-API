<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * Schéma de réponse Gemini pour regrouper profil + parcours à partir de texte (OCR / plusieurs documents concaténés).
 *
 * Les clés correspondent aux domaines métier (tables `user_profiles`, `education`, `experiences`, `certifications`, `user_languages`).
 * Les FK (pays, ville, language_id) sont laissées en texte libre ou indices pour résolution applicative ultérieure.
 */
final class ProfileBundleGeminiSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function responseSchema(): array
    {
        $ls = self::localizedString();

        return [
            'type' => 'OBJECT',
            'properties' => [
                'notes' => [
                    'type' => 'STRING',
                    'description' => 'Incertitudes, conflits entre documents, éléments non classables.',
                ],
                'user_profile' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'first_name' => ['type' => 'STRING'],
                        'last_name' => ['type' => 'STRING'],
                        'date_of_birth' => ['type' => 'STRING', 'description' => 'ISO 8601 date (YYYY-MM-DD) ou vide'],
                        'place_of_birth' => ['type' => 'STRING'],
                        'nationality_country_name' => ['type' => 'STRING'],
                        'residence_city_name' => ['type' => 'STRING'],
                        'address' => ['type' => 'STRING'],
                        'phone_number2' => ['type' => 'STRING'],
                        'phone_number3' => ['type' => 'STRING'],
                        'gender' => ['type' => 'STRING'],
                        'bio' => ['type' => 'STRING'],
                        'marital_status' => ['type' => 'STRING'],
                        'number_of_children' => ['type' => 'INTEGER'],
                        'email_institutional' => ['type' => 'STRING'],
                    ],
                ],
                'educations' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'degree' => ['type' => 'STRING'],
                            'institution_name' => ['type' => 'STRING'],
                            'field_of_study' => ['type' => 'STRING'],
                            'start_date' => ['type' => 'STRING'],
                            'end_date' => ['type' => 'STRING'],
                            'is_current' => ['type' => 'BOOLEAN'],
                            'grade' => ['type' => 'STRING'],
                            'country_name' => ['type' => 'STRING'],
                            'city_name' => ['type' => 'STRING'],
                            'education_level_hint' => ['type' => 'STRING', 'description' => 'Libellé du niveau (ex: Master) pour rapprocher education_levels'],
                        ],
                    ],
                ],
                'experiences' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'job_title' => ['type' => 'STRING'],
                            'company_name' => ['type' => 'STRING'],
                            'company_website' => ['type' => 'STRING'],
                            'start_date' => ['type' => 'STRING'],
                            'end_date' => ['type' => 'STRING'],
                            'is_current' => ['type' => 'BOOLEAN'],
                            'responsibilities' => ['type' => 'STRING'],
                            'achievements' => ['type' => 'STRING'],
                            'country_name' => ['type' => 'STRING'],
                            'city_name' => ['type' => 'STRING'],
                        ],
                    ],
                ],
                'certifications' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING'],
                            'issuing_organization' => ['type' => 'STRING'],
                            'issue_date' => ['type' => 'STRING'],
                            'expiry_date' => ['type' => 'STRING'],
                            'credential_id' => ['type' => 'STRING'],
                            'credential_url' => ['type' => 'STRING'],
                        ],
                    ],
                ],
                'languages' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'language_name' => ['type' => 'STRING', 'description' => 'Nom ou code ISO de la langue'],
                            'proficiency_level' => ['type' => 'STRING', 'description' => 'Niveau libre (ex: B2, courant, notion)'],
                        ],
                    ],
                ],
                'formations' => [
                    'type' => 'ARRAY',
                    'description' => 'Formations continues / certifications courtes non assimilables à `certifications` si besoin.',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'title' => $ls,
                            'organization' => ['type' => 'STRING'],
                            'completed_at' => ['type' => 'STRING'],
                            'summary' => ['type' => 'STRING'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function localizedString(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'fr' => ['type' => 'STRING'],
                'en' => ['type' => 'STRING'],
            ],
        ];
    }
}
