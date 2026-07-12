<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Support;

use App\Core\Domain\Identity\Models\User;

final class LocalizedCopy
{
    public static function userLocale(?User $user): string
    {
        if ($user === null) {
            return 'fr';
        }

        $user->loadMissing('settings');
        $language = strtolower((string) ($user->settings?->language ?? 'fr'));

        return str_starts_with($language, 'en') ? 'en' : 'fr';
    }

    /**
     * @param  string|array<string, string>|null  $value
     */
    public static function pick(string|array|null $value, string $locale, string $fallback = 'fr'): string
    {
        $locale = str_starts_with(strtolower($locale), 'en') ? 'en' : 'fr';

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value) || $value === []) {
            return '';
        }

        $picked = $value[$locale] ?? $value[$fallback] ?? null;
        if (is_string($picked) && $picked !== '') {
            return $picked;
        }

        $first = reset($value);

        return is_string($first) ? $first : '';
    }

    /**
     * @param  array<string, mixed>  $replace
     */
    public static function line(string $key, string $locale, array $replace = []): string
    {
        $locale = str_starts_with(strtolower($locale), 'en') ? 'en' : 'fr';

        return (string) trans($key, $replace, $locale);
    }
}
