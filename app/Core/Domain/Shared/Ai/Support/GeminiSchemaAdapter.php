<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Support;

/**
 * Convertit les schémas de réponse Gemini (types en majuscules) vers JSON Schema (xAI / OpenAI).
 */
final class GeminiSchemaAdapter
{
    /**
     * @param  array<string, mixed>  $geminiSchema
     * @return array<string, mixed>
     */
    public static function toJsonSchema(array $geminiSchema): array
    {
        return self::convertNode($geminiSchema);
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private static function convertNode(array $node): array
    {
        $result = [];

        if (isset($node['type']) && is_string($node['type'])) {
            $result['type'] = self::mapType($node['type']);
        }

        if (isset($node['description']) && is_string($node['description'])) {
            $result['description'] = $node['description'];
        }

        if (isset($node['properties']) && is_array($node['properties'])) {
            $properties = [];
            foreach ($node['properties'] as $key => $property) {
                if (! is_string($key) || ! is_array($property)) {
                    continue;
                }
                $properties[$key] = self::convertNode($property);
            }
            $result['properties'] = $properties;
            $result['additionalProperties'] = false;
        }

        if (isset($node['items']) && is_array($node['items'])) {
            $result['items'] = self::convertNode($node['items']);
        }

        if (isset($node['required']) && is_array($node['required'])) {
            $required = array_values(array_filter($node['required'], is_string(...)));
            if ($required !== []) {
                $result['required'] = $required;
            }
        }

        if (isset($node['enum']) && is_array($node['enum'])) {
            $result['enum'] = $node['enum'];
        }

        if (
            ($result['type'] ?? null) === 'object'
            && isset($result['properties'])
            && is_array($result['properties'])
            && $result['properties'] !== []
        ) {
            // Groq `json_schema` strict exige que chaque clé de `properties` soit listée dans `required`.
            $result['required'] = array_keys($result['properties']);
            $result['additionalProperties'] = false;
        }

        return $result;
    }

    private static function mapType(string $geminiType): string
    {
        return match (strtoupper($geminiType)) {
            'OBJECT' => 'object',
            'ARRAY' => 'array',
            'STRING' => 'string',
            'INTEGER' => 'integer',
            'NUMBER' => 'number',
            'BOOLEAN' => 'boolean',
            default => strtolower($geminiType),
        };
    }
}
