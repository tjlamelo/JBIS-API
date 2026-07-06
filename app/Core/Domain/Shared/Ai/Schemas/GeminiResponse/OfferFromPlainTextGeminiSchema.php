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
    public static function responseSchema(string $scope = 'full'): array
    {
        if ($scope === 'editorial') {
            return OfferEditorialFromPlainTextGeminiSchema::responseSchema();
        }

        return [
            'type' => 'OBJECT',
            'required' => ['description', 'responsibilities', 'requirements'],
            'properties' => [
                'notes' => [
                    'type' => 'STRING',
                    'maxLength' => 200,
                    'description' => 'Laisser vide sauf ambiguïté critique.',
                ],
                'description' => self::localizedString(1000, 'Présentation courte du poste'),
                'responsibilities' => self::localizedString(1800, 'Missions et horaires — obligatoire'),
                'requirements' => self::localizedString(1800, 'Profil candidat — obligatoire'),
                'specific_documents' => self::localizedString(1200, 'Pièces à fournir'),
                'work_mode' => [
                    'type' => 'STRING',
                    'maxLength' => 20,
                    'description' => 'on-site, hybrid ou remote',
                ],
                'salary_min' => ['type' => 'NUMBER'],
                'salary_max' => ['type' => 'NUMBER'],
                'currency' => ['type' => 'STRING', 'maxLength' => 10],
                'is_salary_public' => [
                    'type' => 'BOOLEAN',
                    'description' => 'false par défaut (salaire non affiché publiquement)',
                ],
                'available_positions' => ['type' => 'INTEGER'],
                'address' => ['type' => 'STRING', 'maxLength' => 255],
                'country_hint' => ['type' => 'STRING', 'maxLength' => 60],
                'contract_type_hint' => ['type' => 'STRING', 'maxLength' => 60],
                'inferred_skills' => [
                    'type' => 'ARRAY',
                    'maxItems' => 8,
                    'items' => ['type' => 'STRING', 'maxLength' => 60],
                ],
                'inferred_benefits' => [
                    'type' => 'ARRAY',
                    'maxItems' => 8,
                    'items' => ['type' => 'STRING', 'maxLength' => 60],
                ],
                'inferred_required_documents' => [
                    'type' => 'ARRAY',
                    'maxItems' => 10,
                    'items' => ['type' => 'STRING', 'maxLength' => 80],
                ],
                'language_requirements' => [
                    'type' => 'ARRAY',
                    'maxItems' => 5,
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'language_hint' => ['type' => 'STRING', 'maxLength' => 30],
                            'level_hint' => ['type' => 'STRING', 'maxLength' => 30],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function localizedString(int $maxLength, string $description): array
    {
        return [
            'type' => 'OBJECT',
            'description' => $description,
            'properties' => [
                'fr' => ['type' => 'STRING', 'maxLength' => $maxLength],
                'en' => ['type' => 'STRING', 'maxLength' => $maxLength],
            ],
        ];
    }
}
