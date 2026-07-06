<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\Support;

final class ProcessFlowImportRowParser
{
    public static function string(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    public static function int(mixed $value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    public static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public static function float(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) str_replace([' ', ','], ['', '.'], (string) $value);
    }

    public static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::float($value);
    }

    public static function bool(mixed $value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'oui', 'o', 'y'], true);
    }

    /**
     * @return list<string>
     */
    public static function csvList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map(
                static fn (mixed $item): string => strtoupper(trim((string) $item)),
                $value,
            ), static fn (string $item): bool => $item !== ''));
        }

        return array_values(array_filter(array_map(
            static fn (string $part): string => strtoupper(trim($part)),
            explode(',', (string) $value),
        ), static fn (string $item): bool => $item !== ''));
    }
}
