<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * Schéma réduit pour le remplissage éditorial uniquement (descriptif & missions).
 */
final class OfferEditorialFromPlainTextGeminiSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function responseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'required' => ['description', 'responsibilities', 'requirements'],
            'properties' => [
                'notes' => ['type' => 'STRING', 'maxLength' => 200],
                'description' => self::ls(1000),
                'responsibilities' => self::ls(1800),
                'requirements' => self::ls(1800),
                'specific_documents' => self::ls(1200),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function ls(int $maxLength): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'fr' => ['type' => 'STRING', 'maxLength' => $maxLength],
                'en' => ['type' => 'STRING', 'maxLength' => $maxLength],
            ],
        ];
    }
}
