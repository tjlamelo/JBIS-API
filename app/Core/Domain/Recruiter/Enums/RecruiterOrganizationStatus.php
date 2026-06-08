<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Enums;

enum RecruiterOrganizationStatus: string
{
    case Pending = 'pending';
    case Provisioning = 'provisioning';
    case Active = 'active';
    case Suspended = 'suspended';
    case Failed = 'failed';
}
