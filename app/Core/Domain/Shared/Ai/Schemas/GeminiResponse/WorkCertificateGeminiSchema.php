<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * Attestation / certificat de travail.
 */
final class WorkCertificateGeminiSchema
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
                    'description' => 'Identité du salarié si mentionnée sur l\'attestation.',
                    'properties' => [
                        'first_name' => ['type' => 'STRING'],
                        'last_name' => ['type' => 'STRING'],
                        'full_name' => ['type' => 'STRING'],
                    ],
                ],
                'work_certificate' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'job_title' => ['type' => 'STRING', 'description' => 'Poste / fonction occupée'],
                        'company_name' => ['type' => 'STRING', 'description' => 'Employeur'],
                        'start_date' => ['type' => 'STRING'],
                        'end_date' => ['type' => 'STRING'],
                        'is_current' => ['type' => 'BOOLEAN'],
                        'responsibilities' => ['type' => 'STRING', 'description' => 'Missions ou motif de délivrance'],
                        'city_name' => ['type' => 'STRING'],
                        'country_name' => ['type' => 'STRING'],
                    ],
                ],
            ],
        ];
    }
}
