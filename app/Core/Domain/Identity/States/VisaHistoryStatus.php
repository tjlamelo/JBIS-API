<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\States;

enum VisaHistoryStatus: string
{
    case Granted = 'GRANTED';

    case Refused = 'REFUSED';

    case Expired = 'EXPIRED';

    case Cancelled = 'CANCELLED';
}
