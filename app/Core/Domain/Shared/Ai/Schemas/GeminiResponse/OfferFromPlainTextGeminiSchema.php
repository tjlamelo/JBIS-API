<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Schemas\GeminiResponse;

/**
 * Schéma pour structurer une offre à partir de texte brut (champs alignés sur le modèle Offer + enrichissements).
 */
final class OfferFromPlainTextGeminiSchema
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
                'notes' => ['type' => 'STRING', 'description' => 'Ambiguïtés ou informations manquantes.'],
                'title' => $ls,
                'description' => $ls,
                'responsibilities' => $ls,
                'requirements' => $ls,
                'specific_documents' => $ls,
                'expectations' => $ls,
                'specifications' => $ls,
                'work_mode' => [
                    'type' => 'STRING',
                    'description' => 'Une valeur parmi : on-site, hybrid, remote',
                ],
                'salary_min' => ['type' => 'NUMBER'],
                'salary_max' => ['type' => 'NUMBER'],
                'currency' => ['type' => 'STRING'],
                'is_salary_public' => ['type' => 'BOOLEAN'],
                'available_positions' => ['type' => 'INTEGER'],
                'address' => ['type' => 'STRING'],
                'inferred_skills' => [
                    'type' => 'ARRAY',
                    'items' => ['type' => 'STRING'],
                ],
                'inferred_benefits' => [
                    'type' => 'ARRAY',
                    'items' => ['type' => 'STRING'],
                ],
                'education_level_hint' => ['type' => 'STRING', 'description' => 'Libellé du niveau requis pour rapprocher education_levels'],
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
