<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Enums;

enum ProfileOrigin: string
{
    case Self = 'self';
    case Recruiter = 'recruiter';
    case Staff = 'staff';
}
