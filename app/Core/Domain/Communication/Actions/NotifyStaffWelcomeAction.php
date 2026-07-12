<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Application\Mail\Jobs\SendStaffWelcomeMailJob;
use App\Core\Domain\Communication\Enums\InAppNotificationType;
use App\Core\Domain\Communication\Services\InAppNotificationService;
use App\Core\Domain\Communication\Support\LocalizedCopy;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;

final class NotifyStaffWelcomeAction
{
    public function __construct(
        private readonly InAppNotificationService $notifications,
    ) {}

    public function execute(User $user): void
    {
        $user->loadMissing(['profile', 'settings', 'roles']);

        if (! $user->hasAnyRole(ApplicationRole::STAFF_ROLES)) {
            return;
        }

        $locale = LocalizedCopy::userLocale($user);
        $name = $user->profile?->first_name ?: $user->name ?: ($locale === 'en' ? 'colleague' : 'collègue');
        $title = LocalizedCopy::line('notifications.staff_welcome.title', $locale);
        $body = LocalizedCopy::line('notifications.staff_welcome.body', $locale, ['name' => $name]);

        $this->notifications->notify(
            $user,
            InAppNotificationType::StaffWelcome,
            $title,
            $body,
            [
                'locale' => $locale,
                'roles' => $user->getRoleNames()->values()->all(),
            ],
            'staff_welcome:'.$user->id,
            '/admin/tasks',
        );

        if (filled($user->email)) {
            SendStaffWelcomeMailJob::dispatch($user->id)->onQueue('mail');
        }
    }

    /**
     * @param  list<string>|null  $rolesBefore
     * @param  list<string>|null  $rolesAfter
     */
    public function ifBecameStaff(User $user, ?array $rolesBefore, ?array $rolesAfter): void
    {
        if ($rolesAfter === null) {
            return;
        }

        $beforeStaff = $this->containsStaffRole($rolesBefore ?? []);
        $afterStaff = $this->containsStaffRole($rolesAfter);

        if (! $beforeStaff && $afterStaff) {
            $this->execute($user->fresh(['profile', 'settings', 'roles']) ?? $user);
        }
    }

    /**
     * @param  list<string>  $roles
     */
    private function containsStaffRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if (ApplicationRole::isStaff((string) $role)) {
                return true;
            }
        }

        return false;
    }
}
