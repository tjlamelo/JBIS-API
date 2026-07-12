<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import\Support;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

final class AdminUserImportRowParser
{
    public static function string(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    public static function nullableString(mixed $value): ?string
    {
        $value = self::string($value);

        return $value === '' ? null : $value;
    }

    public static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public static function nullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'oui', 'o', 'y', 'actif', 'active'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'non', 'n', 'inactif', 'inactive'], true)) {
            return false;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function roles(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $parts = preg_split('/[,;|]+/', (string) $value) ?: [];

        return array_values(array_filter(array_map(
            static fn (string $role): string => strtolower(trim($role)),
            $parts,
        )));
    }

    public static function nullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }

            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public static function normalizeGender(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'm', 'h', 'homme', 'male', 'man', 'masculin' => 'M',
            'f', 'femme', 'female', 'woman', 'féminin', 'feminin' => 'F',
            default => strtoupper($value) === 'M' || strtoupper($value) === 'F'
                ? strtoupper($value)
                : null,
        };
    }

    public static function normalizeCivility(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'mr', 'm.', 'm', 'monsieur', 'mister' => 'mr',
            'mrs', 'mme', 'madame', 'ms' => 'mrs',
            'miss', 'mlle', 'mademoiselle' => 'miss',
            default => in_array($normalized, ['mr', 'mrs', 'miss'], true) ? $normalized : null,
        };
    }

    public static function normalizeMaritalStatus(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtoupper(str_replace([' ', '-'], '_', trim($value)));

        return match ($normalized) {
            'SINGLE', 'CELIBATAIRE', 'CÉLIBATAIRE' => 'SINGLE',
            'MARRIED', 'MARIE', 'MARIÉ', 'MARIEE', 'MARIÉE' => 'MARRIED',
            'DIVORCED', 'DIVORCE', 'DIVORCÉ', 'DIVORCEE', 'DIVORCÉE' => 'DIVORCED',
            'WIDOWED', 'VEUF', 'VEUVE' => 'WIDOWED',
            default => in_array($normalized, ['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOWED'], true)
                ? $normalized
                : null,
        };
    }
}
