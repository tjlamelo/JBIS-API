<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Enums;

enum DailyTaskStatus: string
{
    case Completed = 'COMPLETED';
    case Partial = 'PARTIAL';
    case Blocked = 'BLOCKED';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
