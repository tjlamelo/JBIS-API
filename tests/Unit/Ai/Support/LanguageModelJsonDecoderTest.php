<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Support;

use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelInvalidJsonException;
use App\Core\Domain\Shared\Ai\Support\LanguageModelJsonDecoder;
use PHPUnit\Framework\TestCase;

final class LanguageModelJsonDecoderTest extends TestCase
{
    public function test_strips_json_markdown_fence(): void
    {
        $raw = "```json\n{\"a\":1}\n```";
        self::assertSame(['a' => 1], LanguageModelJsonDecoder::decodeObject($raw));
    }

    public function test_invalid_json_throws(): void
    {
        $this->expectException(LanguageModelInvalidJsonException::class);
        LanguageModelJsonDecoder::decodeObject('not json');
    }

    public function test_escapes_unescaped_newlines_inside_json_strings(): void
    {
        $raw = "{\"description\":{\"fr\":\"Ligne 1\nLigne 2\",\"en\":\"Line 1\"}}";

        self::assertSame(
            ['description' => ['fr' => "Ligne 1\nLigne 2", 'en' => 'Line 1']],
            LanguageModelJsonDecoder::decodeObject($raw),
        );
    }

    public function test_strips_control_characters_outside_json_strings(): void
    {
        $raw = "{\"a\":1}\x0B";

        self::assertSame(['a' => 1], LanguageModelJsonDecoder::decodeObject($raw));
    }

    public function test_escapes_vertical_tab_inside_json_strings(): void
    {
        $raw = "{\"notes\":\"hello\x0Bworld\"}";

        self::assertSame(['notes' => 'helloworld'], LanguageModelJsonDecoder::decodeObject($raw));
    }

    public function test_decodes_json_wrapped_with_extra_text(): void
    {
        $raw = "Voici le résultat :\n{\"title\":{\"fr\":\"Test\",\"en\":\"Test\"}}\nMerci.";

        self::assertSame(
            ['title' => ['fr' => 'Test', 'en' => 'Test']],
            LanguageModelJsonDecoder::decodeObject($raw),
        );
    }
}
