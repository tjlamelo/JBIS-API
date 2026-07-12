<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Enums;

use App\Core\Domain\Shared\Support\Concerns\HasEnumLabels;
use App\Core\Domain\Shared\Support\Contracts\LocalizableBackedEnum;

enum Civility: string implements LocalizableBackedEnum
{
    use HasEnumLabels;

    case Mr = 'mr';
    case Mrs = 'mrs';
    case Miss = 'miss';

    public static function translationKey(): string
    {
        return 'civility';
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<string>
     */
    public static function valuesForGender(?string $gender): array
    {
        return match ($gender) {
            'M' => [self::Mr->value],
            'F' => [self::Mrs->value, self::Miss->value],
            default => self::values(),
        };
    }

    public static function defaultForGender(?string $gender): ?string
    {
        return match ($gender) {
            'M' => self::Mr->value,
            'F' => self::Mrs->value,
            default => null,
        };
    }

    public static function normalize(?string $civility, ?string $gender): ?string
    {
        if ($gender === 'M') {
            return self::Mr->value;
        }

        if ($gender === 'F') {
            if (in_array($civility, [self::Mrs->value, self::Miss->value], true)) {
                return $civility;
            }

            return self::Mrs->value;
        }

        return in_array($civility, self::values(), true) ? $civility : null;
    }

    public static function isAllowedForGender(?string $civility, ?string $gender): bool
    {
        if ($civility === null || $civility === '') {
            return true;
        }

        return in_array($civility, self::valuesForGender($gender), true);
    }
}
