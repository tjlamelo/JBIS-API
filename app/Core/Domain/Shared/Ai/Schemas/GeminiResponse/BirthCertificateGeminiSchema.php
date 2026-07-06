<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * Extrait d'acte de naissance — état civil.
 */
final class BirthCertificateGeminiSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function responseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'notes' => ['type' => 'STRING'],
                'user_profile' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'first_name' => ['type' => 'STRING'],
                        'last_name' => ['type' => 'STRING'],
                        'date_of_birth' => ['type' => 'STRING'],
                        'place_of_birth' => ['type' => 'STRING'],
                        'gender' => ['type' => 'STRING'],
                        'nationality_country_name' => ['type' => 'STRING'],
                    ],
                ],
                'birth_record' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'father_name' => ['type' => 'STRING'],
                        'mother_name' => ['type' => 'STRING'],
                        'registration_number' => ['type' => 'STRING'],
                        'issue_date' => ['type' => 'STRING'],
                        'issuing_authority' => ['type' => 'STRING'],
                    ],
                ],
            ],
        ];
    }
}
