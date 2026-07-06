<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Support;

use App\Core\Domain\Shared\Ai\Support\GeminiSchemaAdapter;
use Tests\TestCase;

final class GeminiSchemaAdapterTest extends TestCase
{
    public function test_converts_gemini_types_to_json_schema(): void
    {
        $schema = GeminiSchemaAdapter::toJsonSchema([
            'type' => 'OBJECT',
            'properties' => [
                'name' => ['type' => 'STRING', 'description' => 'Nom complet'],
                'count' => ['type' => 'INTEGER'],
                'active' => ['type' => 'BOOLEAN'],
                'tags' => [
                    'type' => 'ARRAY',
                    'items' => ['type' => 'STRING'],
                ],
            ],
            'required' => ['name'],
        ]);

        self::assertSame('object', $schema['type']);
        self::assertSame('string', $schema['properties']['name']['type']);
        self::assertSame('Nom complet', $schema['properties']['name']['description']);
        self::assertSame('integer', $schema['properties']['count']['type']);
        self::assertSame('boolean', $schema['properties']['active']['type']);
        self::assertSame('array', $schema['properties']['tags']['type']);
        self::assertSame('string', $schema['properties']['tags']['items']['type']);
        self::assertSame(['name', 'count', 'active', 'tags'], $schema['required']);
        self::assertFalse($schema['additionalProperties']);
    }
}
