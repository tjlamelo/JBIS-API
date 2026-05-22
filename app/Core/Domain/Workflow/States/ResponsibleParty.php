<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\States;

enum ResponsibleParty: string
{
    case Candidate = 'CANDIDATE';
    case Jbis = 'JBIS';
    case Employer = 'EMPLOYER';
    case Authority = 'AUTHORITY';
    case Shared = 'SHARED';
}
