<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Enums;

enum PartnerCohortStudentEnrollmentStatus: string
{
    case Invited = 'invited';
    case Registered = 'registered';
    case Active = 'active';
    case Withdrawn = 'withdrawn';
}
