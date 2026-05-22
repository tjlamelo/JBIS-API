<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\States;

enum PaymentType: string
{
    case FileOpening = 'FILE_OPENING';
    case ProcedureInstalment = 'PROCEDURE_INSTALMENT';
    case BlockedAccount = 'BLOCKED_ACCOUNT';
}
