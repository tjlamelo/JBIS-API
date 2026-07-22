<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

/**
 * Fusionne le texte natif PDF et l'OCR pour structurer le CV via LLM texte.
 * Sur CV multi-colonnes, le texte natif est souvent désordonné : l'OCR est prioritaire.
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

        // OCR suffisamment riche → on l'utilise seul (évite le texte PDF jumbled).
        if (mb_strlen($ocr) >= 400 && mb_strlen($ocr) >= (int) (mb_strlen($native) * 0.6)) {
            return $ocr;
        }

        return implode("\n\n", [
            '--- TEXTE OCR (mise en page, PRIORITAIRE) ---',
            $ocr,
            '--- TEXTE PDF NATIF (complément) ---',
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
