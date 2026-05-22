<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Mappers\Shared;

final class TranslatablePayloadNormalizer
{
    /**
     * @return array{fr: string, en: string}
     */
    public static function normalize(mixed $value): array
    {
        if (is_array($value)) {
            return [
                'fr' => (string) ($value['fr'] ?? $value['en'] ?? ''),
                'en' => (string) ($value['en'] ?? $value['fr'] ?? ''),
            ];
        }

        $text = trim((string) $value);

        return ['fr' => $text, 'en' => $text];
    }

    /**
     * @return array{fr: string, en: string}|null
     */
    public static function normalizeNullable(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = self::normalize($value);
        if ($normalized['fr'] === '' && $normalized['en'] === '') {
            return null;
        }

        return $normalized;
    }
}
