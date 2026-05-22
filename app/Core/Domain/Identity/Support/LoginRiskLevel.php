<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

enum LoginRiskLevel: string
{
    case Low = 'low';

    case Medium = 'medium';

    case High = 'high';

    case Critical = 'critical';

    public function shouldNotify(): bool
    {
        return $this !== self::Low;
    }
}
