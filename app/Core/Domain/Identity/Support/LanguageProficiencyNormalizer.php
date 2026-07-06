<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

/**
 * Interprète les niveaux de langue illustrés (●●●, ★★☆, barres) quand le CV n'a pas de texte explicite.
 */
final class LanguageProficiencyNormalizer
{
    public function normalize(?string $value): string
    {
        $input = trim((string) $value);
        if ($input === '') {
            return '';
        }

        if (! $this->containsVisualMarkers($input)) {
            return $input;
        }

        $filled = preg_match_all('/[●■★⬤•▪]/u', $input, $matches) ? count($matches[0]) : 0;
        $empty = preg_match_all('/[○◯□☆▫]/u', $input, $matches) ? count($matches[0]) : 0;

        if ($filled <= 0) {
            return $input;
        }

        if ($empty > 0) {
            $ratio = $filled / ($filled + $empty);

            return match (true) {
                $ratio <= 0.25 => 'notion (A1)',
                $ratio <= 0.45 => 'débutant (A2)',
                $ratio <= 0.65 => 'intermédiaire (B1)',
                $ratio <= 0.85 => 'professionnel (B2)',
                default => 'courant (C1)',
            };
        }

        return match (true) {
            $filled <= 1 => 'notion (A1)',
            $filled === 2 => 'débutant (A2)',
            $filled === 3 => 'intermédiaire (B1)',
            $filled === 4 => 'professionnel (B2)',
            default => 'courant (C1)',
        };
    }

    private function containsVisualMarkers(string $value): bool
    {
        return preg_match('/[●○■□★☆⬤•▪▫]/u', $value) === 1;
    }
}
