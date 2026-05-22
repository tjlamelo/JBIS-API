<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Enums;

enum ChatRole: string
{
    case System = 'system';
    case User = 'user';
    case Assistant = 'assistant';
}
