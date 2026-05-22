<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * Schéma pour classer des offres par adéquation avec un profil candidat.
 */
final class OfferRecommendationGeminiSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function responseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'summary' => ['type' => 'STRING', 'description' => 'Synthèse courte du profil utilisé pour le classement.'],
                'recommendations' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'offer_id' => ['type' => 'INTEGER'],
                            'fit_score' => ['type' => 'NUMBER', 'description' => 'Score entre 0 et 1'],
                            'rationale' => ['type' => 'STRING'],
                            'strengths' => [
                                'type' => 'ARRAY',
                                'items' => ['type' => 'STRING'],
                            ],
                            'gaps' => [
                                'type' => 'ARRAY',
                                'items' => ['type' => 'STRING'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
