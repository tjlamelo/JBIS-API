<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * Certification professionnelle ou attestation de formation.
 */
final class CertificationDocumentGeminiSchema
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
                        'full_name' => ['type' => 'STRING'],
                    ],
                ],
                'certification' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'name' => ['type' => 'STRING', 'description' => 'Intitulé de la certification ou formation'],
                        'issuing_organization' => ['type' => 'STRING'],
                        'issue_date' => ['type' => 'STRING'],
                        'expiry_date' => ['type' => 'STRING'],
                        'credential_id' => ['type' => 'STRING'],
                        'credential_url' => ['type' => 'STRING'],
                        'field_of_study' => ['type' => 'STRING'],
                        'duration' => ['type' => 'STRING'],
                    ],
                ],
            ],
        ];
    }
}
