<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\States;

enum ApplicationStepPaymentStatus: string
{
    case Unpaid = 'UNPAID';
    case Partial = 'PARTIAL';
    case Paid = 'PAID';
    case Overpaid = 'OVERPAID';
    case Waived = 'WAIVED';
}
