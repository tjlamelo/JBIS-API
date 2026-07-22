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
    public function it_merges_native_and_ocr_with_markers(): void
    {
        $preparer = new CvSourceTextPreparer();

        $result = $preparer->prepare('Native header', 'OCR body');

        $this->assertStringContainsString('--- TEXTE OCR (mise en page) ---', $result);
        $this->assertStringContainsString('OCR body', $result);
        $this->assertStringContainsString('--- TEXTE PDF NATIF ---', $result);
        $this->assertStringContainsString('Native header', $result);
    }
}
