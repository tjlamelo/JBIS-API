<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\States;

use App\Core\Domain\Shared\Support\Concerns\HasEnumLabels;
use App\Core\Domain\Shared\Support\Contracts\LocalizableBackedEnum;

enum ApplicationStatus: string implements LocalizableBackedEnum
{
    use HasEnumLabels;

    case Pending = 'PENDING';
    case InProgress = 'IN_PROGRESS';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Cancelled = 'CANCELLED';

    public static function translationKey(): string
    {
        return 'application_status';
    }
}
