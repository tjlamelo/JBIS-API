<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Enums;

enum MeetingStatus: string
{
    case Scheduled = 'SCHEDULED';
    case Ongoing = 'ONGOING';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
