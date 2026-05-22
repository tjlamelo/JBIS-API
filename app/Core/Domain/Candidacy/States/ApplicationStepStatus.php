<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\States;

enum ApplicationStepStatus: string
{
    case Locked = 'LOCKED';
    case Pending = 'PENDING';
    case Completed = 'COMPLETED';
    case Skipped = 'SKIPPED';
}
