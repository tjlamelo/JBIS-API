<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

final class MaritalStatusNormalizer
{
    public function normalize(?string $value): ?string
    {
        $input = mb_strtolower(trim((string) $value));
        if ($input === '') {
            return null;
        }

        if (in_array($input, ['single', 'married', 'divorced', 'widowed'], true)) {
            return strtoupper($input);
        }

        return match (true) {
            preg_match('/\b(célibataire|celibataire|single|non marié|non marie)\b/u', $input) === 1 => 'SINGLE',
            preg_match('/\b(marié|mariée|marie|mariee|married|époux|épouse|epoux|epouse)\b/u', $input) === 1 => 'MARRIED',
            preg_match('/\b(divorcé|divorcée|divorce|divorced)\b/u', $input) === 1 => 'DIVORCED',
            preg_match('/\b(veuf|veuve|widowed|widow)\b/u', $input) === 1 => 'WIDOWED',
            default => null,
        };
    }
}
