<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Enums;

enum AssignedTaskStatus: string
{
    case Todo = 'TODO';
    case InProgress = 'IN_PROGRESS';
    case Done = 'DONE';
    case Cancelled = 'CANCELLED';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
