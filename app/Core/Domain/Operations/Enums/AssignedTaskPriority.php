<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Enums;

enum AssignedTaskPriority: string
{
    case Low = 'LOW';
    case Medium = 'MEDIUM';
    case High = 'HIGH';
    case Urgent = 'URGENT';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
