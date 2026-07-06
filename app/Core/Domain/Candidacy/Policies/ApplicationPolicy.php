<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Policies;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;

final class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('application', ApplicationPermission::VIEW));
    }

    public function view(User $user, Application $application): bool
    {
        if ($application->user_id === $user->id) {
            return true;
        }

        return $user->can(ApplicationPermission::name('application', ApplicationPermission::VIEW));
    }

    public function create(User $user): bool
    {
        return $user->can(ApplicationPermission::name('application', ApplicationPermission::CREATE));
    }

    public function update(User $user, Application $application): bool
    {
        return $user->can(ApplicationPermission::name('application', ApplicationPermission::UPDATE));
    }

    /** Liste staff de tous les dossiers. */
    public function manageAny(User $user): bool
    {
        return $user->can(ApplicationPermission::name('application', ApplicationPermission::UPDATE));
    }

    public function cancel(User $user, Application $application): bool
    {
        if ((int) $application->user_id === (int) $user->id) {
            $status = $application->status instanceof ApplicationStatus
                ? $application->status
                : ApplicationStatus::tryFrom((string) $application->status);

            return in_array($status, [ApplicationStatus::Pending, ApplicationStatus::InProgress], true);
        }

        return $user->can(ApplicationPermission::name('application', ApplicationPermission::UPDATE));
    }

    public function reject(User $user, Application $application): bool
    {
        return $user->can(ApplicationPermission::name('application', ApplicationPermission::UPDATE));
    }

    public function acceptProtocol(User $user, Application $application): bool
    {
        return (int) $application->user_id === (int) $user->id;
    }
}
