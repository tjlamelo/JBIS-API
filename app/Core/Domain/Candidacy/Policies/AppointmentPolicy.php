<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Policies;

use App\Core\Domain\Candidacy\Models\Appointment;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;

final class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::VIEW));
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::UPDATE));
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->can(ApplicationPermission::name('user', ApplicationPermission::DELETE));
    }
}
