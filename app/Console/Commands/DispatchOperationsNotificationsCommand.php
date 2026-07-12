<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Domain\Operations\Actions\DispatchOperationsTaskRemindersAction;
use App\Core\Domain\Operations\Actions\DispatchOperationsWeeklyRecapAction;
use Illuminate\Console\Command;

final class DispatchOperationsNotificationsCommand extends Command
{
    protected $signature = 'operations:dispatch-notifications
                            {--only= : weekly_recap|task_reminders}
                            {--dry-run : Affiche sans envoyer}';

    protected $description = 'Notifications opérations (récap samedi, retards, non-soumission)';

    public function handle(
        DispatchOperationsWeeklyRecapAction $weeklyRecap,
        DispatchOperationsTaskRemindersAction $reminders,
    ): int {
        $only = $this->option('only');

        if ($only === null || $only === 'weekly_recap') {
            $result = $weeklyRecap->execute();
            $this->info('Weekly recap: '.json_encode($result, JSON_UNESCAPED_UNICODE));
        }

        if ($only === null || $only === 'task_reminders') {
            $result = $reminders->execute();
            $this->info('Task reminders: '.json_encode($result, JSON_UNESCAPED_UNICODE));
        }

        return self::SUCCESS;
    }
}
