<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

final class GenderNormalizer
{
    public function normalize(?string $value): ?string
    {
        $input = mb_strtolower(trim((string) $value));
        if ($input === '') {
            return null;
        }

        return match (true) {
            in_array($input, ['m', 'male', 'homme', 'masculin', 'h'], true) => 'M',
            in_array($input, ['f', 'female', 'femme', 'féminin', 'feminin'], true) => 'F',
            str_contains($input, 'mascul') => 'M',
            str_contains($input, 'femin') || str_contains($input, 'fémin') => 'F',
            default => in_array(strtoupper($input), ['M', 'F'], true) ? strtoupper($input) : null,
        };
    }
}
