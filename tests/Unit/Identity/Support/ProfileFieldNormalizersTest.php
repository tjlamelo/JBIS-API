<?php

declare(strict_types=1);

namespace Tests\Unit\Identity\Support;

use App\Core\Domain\Identity\Support\LanguageProficiencyNormalizer;
use App\Core\Domain\Identity\Support\MaritalStatusNormalizer;
use PHPUnit\Framework\TestCase;

final class ProfileFieldNormalizersTest extends TestCase
{
    public function test_normalizes_visual_language_levels(): void
    {
        $normalizer = new LanguageProficiencyNormalizer();

        self::assertSame('intermédiaire (B1)', $normalizer->normalize('●●●'));
        self::assertSame('débutant (A2)', $normalizer->normalize('●●○○○'));
    }

    public function test_normalizes_marital_status_labels(): void
    {
        $normalizer = new MaritalStatusNormalizer();

        self::assertSame('SINGLE', $normalizer->normalize('Célibataire'));
        self::assertSame('MARRIED', $normalizer->normalize('Marié'));
    }
}
