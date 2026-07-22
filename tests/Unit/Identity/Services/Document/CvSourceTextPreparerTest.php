<?php

declare(strict_types=1);

namespace Tests\Unit\Identity\Services\Document;

use App\Core\Domain\Identity\Services\Document\CvSourceTextPreparer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CvSourceTextPreparerTest extends TestCase
{
    #[Test]
    public function it_returns_ocr_when_native_is_empty(): void
    {
        $preparer = new CvSourceTextPreparer();

        $result = $preparer->prepare('', 'Experience line');

        $this->assertSame('Experience line', $result);
    }

    #[Test]
    public function it_prefers_rich_ocr_alone(): void
    {
        $preparer = new CvSourceTextPreparer();
        $ocr = str_repeat('PROFESSIONAL EXPERIENCE Company X Developer ', 20);
        $native = 'Name Email Phone';

        $result = $preparer->prepare($native, $ocr);

        $this->assertSame(trim($ocr), $result);
        $this->assertStringNotContainsString('TEXTE PDF NATIF', $result);
    }

    #[Test]
    public function it_merges_when_ocr_is_short(): void
    {
        $preparer = new CvSourceTextPreparer();

        $result = $preparer->prepare('Native header with more content here', 'OCR short');

        $this->assertStringContainsString('--- TEXTE OCR (mise en page, PRIORITAIRE) ---', $result);
        $this->assertStringContainsString('OCR short', $result);
        $this->assertStringContainsString('--- TEXTE PDF NATIF (complément) ---', $result);
    }
}
