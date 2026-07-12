<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Application\Mail\Jobs\SendCameroonHolidayMailJob;
use App\Core\Domain\Communication\Enums\InAppNotificationType;
use App\Core\Domain\Communication\Services\InAppNotificationService;
use App\Core\Domain\Communication\Support\CameroonHolidayCalendar;
use App\Core\Domain\Communication\Support\LocalizedCopy;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class DispatchScheduledInAppNotificationsAction
{
    private const CHUNK = 200;

    public function __construct(
        private readonly InAppNotificationService $notifications,
        private readonly CameroonHolidayCalendar $holidays,
    ) {}

    /**
     * @return array{week_start: int, weekend: int, holidays: int, holiday_emails: int, birthdays: int, birthday_followups: int}
     */
    public function execute(?CarbonImmutable $now = null, ?string $only = null): array
    {
        $tz = (string) config('cameroon_holidays.timezone', 'Africa/Douala');
        $now ??= CarbonImmutable::now($tz);
        $today = $now->toDateString();

        $stats = [
            'week_start' => 0,
            'weekend' => 0,
            'holidays' => 0,
            'holiday_emails' => 0,
            'birthdays' => 0,
            'birthday_followups' => 0,
        ];

        if ($only === null || $only === 'week_start') {
            if ($now->isMonday()) {
                $stats['week_start'] = $this->broadcastAudienceAware(
                    InAppNotificationType::WeekStart,
                    'notifications.week_start',
                    "week_start:{$today}",
                    '/dashboard',
                );
            }
        }

        if ($only === null || $only === 'weekend') {
            if ($now->isFriday() && (int) $now->format('H') >= 17) {
                $stats['weekend'] = $this->broadcastAudienceAware(
                    InAppNotificationType::Weekend,
                    'notifications.weekend',
                    "weekend:{$today}",
                    '/dashboard',
                );
            }
        }

        if ($only === null || $only === 'holidays') {
            foreach ($this->holidays->forDate($now) as $event) {
                $result = $this->broadcastHoliday($event, $today);
                $stats['holidays'] += $result['created'];
                $stats['holiday_emails'] += $result['emails'];
            }
        }

        if ($only === null || $only === 'birthdays') {
            $stats['birthdays'] = $this->dispatchBirthdays($now, false);
            $stats['birthday_followups'] = $this->dispatchBirthdays($now->subDay(), true);
        }

        return $stats;
    }

    /**
     * @param  array{code: string, title: array<string, string>|string, body: array<string, string>|string, date: string}  $event
     * @return array{created: int, emails: int}
     */
    private function broadcastHoliday(array $event, string $today): array
    {
        $created = 0;
        $emails = 0;
        $dedupeKey = "holiday:{$event['code']}:{$today}";

        User::query()
            ->whereNotNull('email_verified_at')
            ->with('settings')
            ->orderBy('id')
            ->chunkById(self::CHUNK, function ($users) use ($event, $dedupeKey, &$created, &$emails): void {
                foreach ($users as $user) {
                    $locale = LocalizedCopy::userLocale($user);
                    $title = LocalizedCopy::pick($event['title'], $locale);
                    $body = LocalizedCopy::pick($event['body'], $locale);

                    $before = DB::table('user_notifications')
                        ->where('user_id', $user->id)
                        ->where('dedupe_key', $dedupeKey)
                        ->exists();

                    $this->notifications->notify(
                        $user,
                        InAppNotificationType::Holiday,
                        $title,
                        $body,
                        [
                            'holiday_code' => $event['code'],
                            'date' => $event['date'],
                            'locale' => $locale,
                        ],
                        $dedupeKey,
                        '/dashboard',
                    );

                    if (! $before) {
                        $created++;

                        if (filled($user->email)) {
                            SendCameroonHolidayMailJob::dispatch(
                                (int) $user->id,
                                $title,
                                $body,
                                $event['code'],
                                $event['date'],
                                $locale,
                            );
                            $emails++;
                        }
                    }
                }
            });

        return compact('created', 'emails');
    }

    private function broadcastAudienceAware(
        InAppNotificationType $type,
        string $baseKey,
        string $dedupeKey,
        ?string $actionUrl = null,
    ): int {
        $created = 0;

        User::query()
            ->whereNotNull('email_verified_at')
            ->with(['settings', 'roles'])
            ->orderBy('id')
            ->chunkById(self::CHUNK, function ($users) use ($type, $baseKey, $dedupeKey, $actionUrl, &$created): void {
                foreach ($users as $user) {
                    $locale = LocalizedCopy::userLocale($user);
                    $audience = $this->audienceKey($user);
                    $title = LocalizedCopy::line("{$baseKey}.{$audience}.title", $locale);
                    $body = LocalizedCopy::line("{$baseKey}.{$audience}.body", $locale);

                    $before = DB::table('user_notifications')
                        ->where('user_id', $user->id)
                        ->where('dedupe_key', $dedupeKey)
                        ->exists();

                    $this->notifications->notify(
                        $user,
                        $type,
                        $title,
                        $body,
                        ['locale' => $locale, 'audience' => $audience],
                        $dedupeKey,
                        $audience === 'staff' ? '/admin/tasks' : $actionUrl,
                    );

                    if (! $before) {
                        $created++;
                    }
                }
            });

        return $created;
    }

    private function dispatchBirthdays(CarbonImmutable $day, bool $followUp): int
    {
        $created = 0;
        $year = $day->year;
        $type = $followUp ? InAppNotificationType::BirthdayFollowUp : InAppNotificationType::Birthday;
        $dedupePrefix = $followUp ? 'birthday_followup' : 'birthday';
        $baseKey = $followUp ? 'notifications.birthday_followup' : 'notifications.birthday';

        User::query()
            ->whereNotNull('email_verified_at')
            ->whereHas('profile', function ($query) use ($day): void {
                $query->whereNotNull('date_of_birth')
                    ->whereMonth('date_of_birth', $day->month)
                    ->whereDay('date_of_birth', $day->day);
            })
            ->with(['profile', 'settings', 'roles'])
            ->orderBy('id')
            ->chunkById(self::CHUNK, function ($users) use ($type, $dedupePrefix, $year, $baseKey, &$created): void {
                foreach ($users as $user) {
                    $locale = LocalizedCopy::userLocale($user);
                    $audience = $this->audienceKey($user);
                    $firstName = $user->profile?->first_name ?: $user->name ?: ($locale === 'en' ? 'friend' : 'ami(e)');
                    $dedupeKey = "{$dedupePrefix}:{$year}";
                    $title = LocalizedCopy::line("{$baseKey}.{$audience}.title", $locale);
                    $body = LocalizedCopy::line("{$baseKey}.{$audience}.body", $locale, ['name' => $firstName]);

                    $before = DB::table('user_notifications')
                        ->where('user_id', $user->id)
                        ->where('dedupe_key', $dedupeKey)
                        ->exists();

                    $this->notifications->notify(
                        $user,
                        $type,
                        $title,
                        $body,
                        [
                            'date_of_birth' => $user->profile?->date_of_birth?->toDateString(),
                            'locale' => $locale,
                            'audience' => $audience,
                        ],
                        $dedupeKey,
                        $audience === 'staff' ? '/admin/tasks' : '/dashboard',
                    );

                    if (! $before) {
                        $created++;
                    }
                }
            });

        return $created;
    }

    private function audienceKey(User $user): string
    {
        return $user->hasAnyRole(ApplicationRole::STAFF_ROLES) ? 'staff' : 'candidate';
    }
}
