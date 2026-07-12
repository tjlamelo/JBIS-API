<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Support;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Operations\Models\Meeting;
use Illuminate\Validation\ValidationException;

final class StaffUserResolver
{
    public static function assertStaffUserId(int $userId): User
    {
        $user = User::query()->find($userId);
        if ($user === null || ! $user->hasAnyRole(ApplicationRole::STAFF_ROLES)) {
            throw ValidationException::withMessages([
                'organizer_id' => [__('L\'organisateur doit être un membre du staff.')],
            ]);
        }

        return $user;
    }

    /**
     * @param  list<int>  $userIds
     * @return list<int>
     */
    public static function filterStaffIds(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return User::query()
            ->role(ApplicationRole::STAFF_ROLES)
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public static function canManageMeetingTasks(User $user, ?Meeting $meeting): bool
    {
        if ($user->hasAnyRole([ApplicationRole::SUPERADMIN, ApplicationRole::ADMIN])) {
            return true;
        }

        if ($meeting === null) {
            return $user->hasAnyRole(ApplicationRole::STAFF_ROLES);
        }

        if ((int) $meeting->organizer_id === (int) $user->id) {
            return true;
        }

        $attendee = $meeting->attendees()
            ->where('users.id', $user->id)
            ->first();

        return $attendee !== null && (bool) $attendee->pivot?->is_present;
    }
}
