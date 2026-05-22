<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\States;

enum ProcessStepType: string
{
    case DocumentCollection = 'DOCUMENT_COLLECTION';
    case Payment = 'PAYMENT';
    case Service = 'SERVICE';
    case Interview = 'INTERVIEW';
    case Signing = 'SIGNING';
    case Administrative = 'ADMINISTRATIVE';
    case ImmigrationExit = 'IMMIGRATION_EXIT';
    case Info = 'INFO';
}
