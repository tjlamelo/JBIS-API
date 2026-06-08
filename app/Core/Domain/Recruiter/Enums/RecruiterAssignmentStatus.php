<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Enums;

enum RecruiterAssignmentStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
}
