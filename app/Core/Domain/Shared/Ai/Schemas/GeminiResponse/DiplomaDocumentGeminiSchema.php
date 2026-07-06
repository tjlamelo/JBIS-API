<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * Diplôme ou relevé de notes.
 */
final class DiplomaDocumentGeminiSchema
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
                'education' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'degree' => ['type' => 'STRING'],
                        'institution_name' => ['type' => 'STRING'],
                        'field_of_study' => ['type' => 'STRING'],
                        'start_date' => ['type' => 'STRING'],
                        'end_date' => ['type' => 'STRING'],
                        'grade' => ['type' => 'STRING'],
                        'country_name' => ['type' => 'STRING'],
                        'city_name' => ['type' => 'STRING'],
                    ],
                ],
            ],
        ];
    }
}
