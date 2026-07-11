<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Enums;

enum PartnerCohortStudentPlacementStatus: string
{
    case Pending = 'pending';
    case Matched = 'matched';
    case Placed = 'placed';
    case Completed = 'completed';
}
