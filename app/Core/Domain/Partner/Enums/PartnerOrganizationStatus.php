<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Enums;

enum PartnerOrganizationStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
}
