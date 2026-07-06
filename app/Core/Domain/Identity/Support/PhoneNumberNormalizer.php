<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

final class PhoneNumberNormalizer
{
    public function normalize(?string $phone, ?string $countryHint = null): ?string
    {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/[^\d+]/', '', $raw) ?? $raw;
        if (str_starts_with($digits, '00')) {
            $digits = '+'.substr($digits, 2);
        }

        if (! str_starts_with($digits, '+')) {
            $digits = ltrim($digits, '0');
            $prefix = $this->defaultPrefixForCountry($countryHint);
            if ($prefix !== null) {
                $digits = $prefix.$digits;
            } else {
                $digits = '+'.$digits;
            }
        }

        return $digits;
    }

    private function defaultPrefixForCountry(?string $countryHint): ?string
    {
        $hint = strtolower(trim((string) $countryHint));
        if ($hint === '') {
            return null;
        }

        return match (true) {
            str_contains($hint, 'cameroun') || str_contains($hint, 'cameroon') => '+237',
            str_contains($hint, 'france') => '+33',
            str_contains($hint, 'côte') || str_contains($hint, 'ivoire') || str_contains($hint, 'ivory') => '+225',
            str_contains($hint, 'sénégal') || str_contains($hint, 'senegal') => '+221',
            str_contains($hint, 'gabon') => '+241',
            str_contains($hint, 'congo') => '+242',
            default => null,
        };
    }
}
