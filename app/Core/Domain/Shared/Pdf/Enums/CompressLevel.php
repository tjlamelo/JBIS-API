<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Enums;

enum CompressLevel: string
{
    case Low = 'low';
    case Recommended = 'recommended';
    case Extreme = 'extreme';

    public static function fromConfig(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Recommended;
    }
}
