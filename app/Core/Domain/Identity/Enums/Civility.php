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
}
