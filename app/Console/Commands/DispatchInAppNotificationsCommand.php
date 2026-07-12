<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Domain\Communication\Actions\DispatchScheduledInAppNotificationsAction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

final class DispatchInAppNotificationsCommand extends Command
{
    protected $signature = 'notifications:dispatch
        {--date= : Date de référence YYYY-MM-DD (timezone Africa/Douala)}
        {--only= : week_start|weekend|holidays|birthdays}';

    protected $description = 'Diffuse les notifications internes planifiées (semaine, week-end, fêtes CM, anniversaires)';

    public function handle(DispatchScheduledInAppNotificationsAction $action): int
    {
        $tz = (string) config('cameroon_holidays.timezone', 'Africa/Douala');
        $dateOption = $this->option('date');
        $now = is_string($dateOption) && $dateOption !== ''
            ? CarbonImmutable::parse($dateOption, $tz)->setTimeFrom(CarbonImmutable::now($tz))
            : CarbonImmutable::now($tz);

        $only = $this->option('only');
        $only = is_string($only) && $only !== '' ? $only : null;

        $stats = $action->execute($now, $only);

        $this->info(sprintf(
            'Notifications — week_start:%d weekend:%d holidays:%d holiday_emails:%d birthdays:%d followups:%d',
            $stats['week_start'],
            $stats['weekend'],
            $stats['holidays'],
            $stats['holiday_emails'] ?? 0,
            $stats['birthdays'],
            $stats['birthday_followups'],
        ));

        return self::SUCCESS;
    }
}
