<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Policies;

use App\Core\Domain\Operations\Policies\Concerns\ChecksStaffOperationsAccess;

final class AssignedTaskPolicy
{
    use ChecksStaffOperationsAccess;

    protected function resourceKey(): string
    {
        return 'assignedtask';
    }
}
