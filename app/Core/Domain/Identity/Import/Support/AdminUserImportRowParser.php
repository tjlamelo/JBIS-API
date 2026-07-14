<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import\Support;

use App\Core\Domain\Identity\Enums\CareerIntent;
use App\Core\Domain\Identity\Enums\ProfileType;
use App\Core\Domain\Identity\Support\PhoneNumberNormalizer;
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

    public static function normalizeEmail(mixed $value): string
    {
        return strtolower(self::string($value));
    }

    public static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
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

    /**
     * @return array{first_name: ?string, last_name: ?string}
     */
    public static function splitFullName(?string $fullName): array
    {
        $fullName = self::nullableString($fullName);
        if ($fullName === null) {
            return ['first_name' => null, 'last_name' => null];
        }

        $parts = preg_split('/\s+/', $fullName) ?: [];
        $parts = array_values(array_filter($parts));

        if ($parts === []) {
            return ['first_name' => null, 'last_name' => null];
        }

        if (count($parts) === 1) {
            return ['first_name' => $parts[0], 'last_name' => $parts[0]];
        }

        return [
            'first_name' => $parts[0],
            'last_name' => implode(' ', array_slice($parts, 1)),
        ];
    }

    /**
     * Remplit first/last à partir des colonnes disponibles (Excel souvent incomplet).
     *
     * @return array{0: ?string, 1: ?string}
     */
    public static function resolveFirstAndLastName(?string $firstName, ?string $lastName, ?string $fullName): array
    {
        $firstName = self::nullableString($firstName);
        $lastName = self::nullableString($lastName);
        $fullName = self::nullableString($fullName);

        if (($firstName === null || $lastName === null) && $fullName !== null) {
            $parts = self::splitFullName($fullName);
            $firstName ??= $parts['first_name'];
            $lastName ??= $parts['last_name'];
        }

        // Tout collé dans first_name (cas fréquent Excel).
        if ($lastName === null && $firstName !== null && preg_match('/\s+/', $firstName) === 1) {
            $parts = self::splitFullName($firstName);
            $firstName = $parts['first_name'];
            $lastName = $parts['last_name'];
        }

        // Tout collé dans last_name.
        if ($firstName === null && $lastName !== null && preg_match('/\s+/', $lastName) === 1) {
            $parts = self::splitFullName($lastName);
            $firstName = $parts['first_name'];
            $lastName = $parts['last_name'];
        }

        // Un seul mot : réutiliser pour les deux (évite blocage d'import).
        if ($firstName !== null && $lastName === null) {
            $lastName = $firstName;
        }
        if ($lastName !== null && $firstName === null) {
            $firstName = $lastName;
        }

        return [$firstName, $lastName];
    }

    public static function normalizePhone(?string $value, ?string $countryHint = null): ?string
    {
        return (new PhoneNumberNormalizer)->normalize($value, $countryHint);
    }

    public static function phoneFingerprint(?string $value, ?string $countryHint = null): ?string
    {
        return (new PhoneNumberNormalizer)->fingerprint($value, $countryHint);
    }

    /**
     * Découpe « 6xxxx/6yyyy » (ou ; | ,) vers phone1 / phone2 / phone3.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    public static function splitAndNormalizePhones(?string $value, ?string $countryHint = null): array
    {
        $value = self::nullableString($value);
        if ($value === null) {
            return [null, null, null];
        }

        $parts = preg_split('/[\/|;,]+/', $value) ?: [];
        $normalized = [];
        $seen = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }

            $phone = self::normalizePhone($part, $countryHint);
            if ($phone === null || strlen($phone) > 20) {
                continue;
            }

            $fp = self::phoneFingerprint($phone, $countryHint);
            if ($fp === null || isset($seen[$fp])) {
                continue;
            }

            $seen[$fp] = true;
            $normalized[] = $phone;

            if (count($normalized) >= 3) {
                break;
            }
        }

        return [
            $normalized[0] ?? null,
            $normalized[1] ?? null,
            $normalized[2] ?? null,
        ];
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

    public static function normalizeCareerIntent(?string $value): ?string
    {
        $value = self::nullableString($value);
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(str_replace([' ', '-'], '_', $value));
        $aliases = [
            'work_abroad' => 'work_abroad',
            'travailler_a_letranger' => 'work_abroad',
            'abroad' => 'work_abroad',
            'work_local' => 'work_local',
            'local' => 'work_local',
            'visa_support' => 'visa_support',
            'visa' => 'visa_support',
            'explore' => 'explore',
            'explorer' => 'explore',
        ];

        $candidate = $aliases[$normalized] ?? $normalized;

        return in_array($candidate, CareerIntent::values(), true) ? $candidate : null;
    }

    public static function normalizeProfileType(?string $value): ?string
    {
        $value = self::nullableString($value);
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(str_replace([' ', '-'], '_', $value));
        $aliases = [
            'student' => 'student',
            'etudiant' => 'student',
            'étudiant' => 'student',
            'recent_graduate' => 'recent_graduate',
            'jeune_diplome' => 'recent_graduate',
            'active_worker' => 'active_worker',
            'actif' => 'active_worker',
            'job_seeker' => 'job_seeker',
            'demandeur_emploi' => 'job_seeker',
            'exploring' => 'exploring',
            'exploration' => 'exploring',
        ];

        $candidate = $aliases[$normalized] ?? $normalized;

        return in_array($candidate, ProfileType::values(), true) ? $candidate : null;
    }
}
