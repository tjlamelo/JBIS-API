<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Enums;

enum NewsletterScope: string
{
    case National = 'national';
    case International = 'international';
    case Both = 'both';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
