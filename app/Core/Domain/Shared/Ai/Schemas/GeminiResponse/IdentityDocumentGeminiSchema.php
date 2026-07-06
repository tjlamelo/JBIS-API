<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * CNI, passeport, titre de séjour — identité + métadonnées pièce.
 */
final class IdentityDocumentGeminiSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function responseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'notes' => [
                    'type' => 'STRING',
                    'description' => 'Incertitudes ou champs illisibles.',
                ],
                'user_profile' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'first_name' => ['type' => 'STRING'],
                        'last_name' => ['type' => 'STRING'],
                        'date_of_birth' => ['type' => 'STRING', 'description' => 'YYYY-MM-DD ou vide'],
                        'place_of_birth' => ['type' => 'STRING'],
                        'nationality_country_name' => ['type' => 'STRING'],
                        'gender' => ['type' => 'STRING'],
                        'address' => ['type' => 'STRING'],
                    ],
                ],
                'user_document' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'document_number' => ['type' => 'STRING'],
                        'issue_date' => ['type' => 'STRING', 'description' => 'YYYY-MM-DD ou vide'],
                        'expiry_date' => ['type' => 'STRING', 'description' => 'YYYY-MM-DD ou vide'],
                        'issuing_country_name' => ['type' => 'STRING'],
                    ],
                ],
            ],
        ];
    }
}
