<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Enums;

enum CareerIntent: string
{
    case WorkAbroad = 'work_abroad';
    case WorkLocal = 'work_local';
    case VisaSupport = 'visa_support';
    case Explore = 'explore';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
