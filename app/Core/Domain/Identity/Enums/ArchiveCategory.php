<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Enums;

enum ArchiveCategory: string
{
    case Rh = 'RH';
    case Admin = 'ADMIN';
    case Legal = 'LEGAL';
    case Finance = 'FINANCE';
    case Logs = 'LOGS';
    case Commercial = 'COMMERCIAL';
    case Other = 'OTHER';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
