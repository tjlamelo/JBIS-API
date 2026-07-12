<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Support;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Operations\Models\AssignedTask;
use App\Core\Domain\Operations\Models\Meeting;

/**
 * Règles d’accès opérations (réunions / tâches) — style Jira.
 *
 * - Admin / superadmin : tout voir et tout gérer.
 * - operations.manage_meetings : créer des réunions et assigner des tâches à des collaborateurs.
 * - operations.view_all_tasks : voir le board de toute l’équipe.
 * - Staff sans ces droits : uniquement ses tâches assignées (statut / notes).
 */
final class OperationsAccess
{
    public const MANAGE_MEETINGS = 'operations.manage_meetings';

    public const VIEW_ALL_TASKS = 'operations.view_all_tasks';

    public static function isAdmin(User $user): bool
    {
        return $user->hasAnyRole([ApplicationRole::SUPERADMIN, ApplicationRole::ADMIN]);
    }

    public static function canManageMeetings(User $user): bool
    {
        return self::isAdmin($user) || $user->can(self::MANAGE_MEETINGS);
    }

    public static function canViewAllTasks(User $user): bool
    {
        return self::isAdmin($user) || $user->can(self::VIEW_ALL_TASKS);
    }

    public static function canAssignToOthers(User $user, ?Meeting $meeting = null): bool
    {
        if (self::canManageMeetings($user)) {
            return true;
        }

        if ($meeting !== null && (int) $meeting->organizer_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    public static function canCreateMeeting(User $user): bool
    {
        return self::canManageMeetings($user)
            || $user->can(ApplicationPermission::name('meeting', ApplicationPermission::CREATE));
    }

    public static function isAssignee(User $user, AssignedTask $task): bool
    {
        if ($task->relationLoaded('assignees')) {
            return $task->assignees->contains('id', $user->id);
        }

        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    public static function canViewTask(User $user, AssignedTask $task): bool
    {
        if (self::canViewAllTasks($user)) {
            return true;
        }

        if ((int) $task->created_by === (int) $user->id) {
            return true;
        }

        return self::isAssignee($user, $task);
    }

    public static function canUpdateTask(User $user, AssignedTask $task): bool
    {
        return self::canViewTask($user, $task);
    }

    /**
     * @return array{view_all_tasks: bool, manage_meetings: bool, can_create_meeting: bool}
     */
    public static function capabilities(User $user): array
    {
        return [
            'view_all_tasks' => self::canViewAllTasks($user),
            'manage_meetings' => self::canManageMeetings($user),
            'can_create_meeting' => self::canCreateMeeting($user),
        ];
    }
}
