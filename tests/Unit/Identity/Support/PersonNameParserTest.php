<?php

declare(strict_types=1);

namespace Tests\Unit\Identity\Support;

use App\Core\Domain\Identity\Support\PersonNameParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PersonNameParserTest extends TestCase
{
    private PersonNameParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new PersonNameParser;
    }

    #[DataProvider('africanNameProvider')]
    public function test_parses_african_full_names(string $fullName, string $expectedFirst, string $expectedLast): void
    {
        $parsed = $this->parser->parse(null, null, $fullName);

        self::assertSame($expectedFirst, $parsed['first_name']);
        self::assertSame($expectedLast, $parsed['last_name']);
        self::assertSame($fullName, $parsed['full_name']);
    }

    public static function africanNameProvider(): array
    {
        return [
            'two family names and two given names' => ['ATANGANA OWONA Francois Mavis', 'Francois Mavis', 'ATANGANA OWONA'],
            'single family name western order' => ['DUPONT Jean', 'Jean', 'DUPONT'],
            'all uppercase four parts' => ['ATANGANA OWONA FRANCOIS MAVIS', 'Francois Mavis', 'ATANGANA OWONA'],
            'given name first then family names' => ['Hilaire TAMAKUE GUIFO', 'Hilaire', 'TAMAKUE GUIFO'],
            'all title case three parts' => ['Hilaire Tamakue Guifo', 'Hilaire', 'TAMAKUE GUIFO'],
        ];
    }

    public function test_fixes_ai_duplicate_name_fields(): void
    {
        $parsed = $this->parser->parse('Hilaire Tamakue Guifo', 'HILAIRE TAMAKUE GUIFO');

        self::assertSame('Hilaire', $parsed['first_name']);
        self::assertSame('TAMAKUE GUIFO', $parsed['last_name']);
    }

    public function test_fixes_ai_duplicate_with_full_name_hint(): void
    {
        $parsed = $this->parser->parse(
            'Hilaire Tamakue Guifo',
            'HILAIRE TAMAKUE GUIFO',
            'Hilaire TAMAKUE GUIFO',
        );

        self::assertSame('Hilaire', $parsed['first_name']);
        self::assertSame('TAMAKUE GUIFO', $parsed['last_name']);
        self::assertSame('Hilaire TAMAKUE GUIFO', $parsed['full_name']);
    }

    public function test_preserves_all_parts_when_fields_are_already_correct(): void
    {
        $parsed = $this->parser->parse('Francois Mavis', 'ATANGANA OWONA', 'ATANGANA OWONA Francois Mavis');

        self::assertSame('Francois Mavis', $parsed['first_name']);
        self::assertSame('ATANGANA OWONA', $parsed['last_name']);
    }

    public function test_swaps_inverted_first_and_last_fields(): void
    {
        $parsed = $this->parser->parse('DUPONT', 'Jean');

        self::assertSame('Jean', $parsed['first_name']);
        self::assertSame('DUPONT', $parsed['last_name']);
    }
}
