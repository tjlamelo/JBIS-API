<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Enums;

use App\Core\Domain\Shared\Support\Concerns\HasEnumLabels;
use App\Core\Domain\Shared\Support\Contracts\LocalizableBackedEnum;

enum CareerIntent: string implements LocalizableBackedEnum
{
    use HasEnumLabels;

    case WorkAbroad = 'work_abroad';
    case WorkLocal = 'work_local';
    case VisaSupport = 'visa_support';
    case Explore = 'explore';

    public static function translationKey(): string
    {
        return 'career_intent';
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
