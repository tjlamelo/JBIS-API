<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

/**
 * Fusionne le texte natif PDF et l'OCR pour structurer le CV via LLM texte.
 */
final class CvSourceTextPreparer
{
    public function prepare(string $nativeText, string $ocrText): string
    {
        $native = $this->normalize($nativeText);
        $ocr = $this->normalize($ocrText);

        if ($native === '' && $ocr === '') {
            return '';
        }

        if ($native === '') {
            return $ocr;
        }

        if ($ocr === '') {
            return $native;
        }

        return implode("\n\n", [
            '--- TEXTE OCR (mise en page) ---',
            $ocr,
            '--- TEXTE PDF NATIF ---',
            $native,
        ]);
    }

    private function normalize(string $text): string
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return '';
        }

        return preg_replace("/\n{3,}/", "\n\n", $trimmed) ?? $trimmed;
    }
}
