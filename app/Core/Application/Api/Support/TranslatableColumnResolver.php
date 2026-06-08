<?php

declare(strict_types=1);

namespace App\Core\Application\Api\Support;

final class TranslatableColumnResolver
{
    public static function resolve(mixed $value, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if (is_array($value)) {
            return (string) ($value[$locale] ?? $value['fr'] ?? $value['en'] ?? reset($value) ?: '');
        }

        if (! is_string($value) || $value === '') {
            return '';
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return (string) ($decoded[$locale] ?? $decoded['fr'] ?? $decoded['en'] ?? $value);
        }

        return $value;
    }
}
