<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\States;

enum ApplicationStatus: string
{
    case Pending = 'PENDING';
    case InProgress = 'IN_PROGRESS';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Cancelled = 'CANCELLED';
}
