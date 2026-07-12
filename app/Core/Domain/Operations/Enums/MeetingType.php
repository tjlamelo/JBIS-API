<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Enums;

enum MeetingType: string
{
    case Online = 'ONLINE';
    case Onsite = 'ONSITE';
    case Hybrid = 'HYBRID';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
