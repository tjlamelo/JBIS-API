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
}
