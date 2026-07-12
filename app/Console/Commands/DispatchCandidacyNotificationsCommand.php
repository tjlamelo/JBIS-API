<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Domain\Candidacy\Actions\DispatchCandidacyRemindersAction;
use Illuminate\Console\Command;

final class DispatchCandidacyNotificationsCommand extends Command
{
    protected $signature = 'candidacy:dispatch-notifications
                            {--only= : documents|payments}
                            {--dry-run : Affiche sans envoyer (non supporté — compte uniquement)}';

    protected $description = 'Rappels candidature (documents manquants, paiements à venir / en retard)';

    public function handle(DispatchCandidacyRemindersAction $reminders): int
    {
        $only = $this->option('only');
        if ($only !== null && ! in_array($only, ['documents', 'payments'], true)) {
            $this->error('Option --only invalide. Utilisez documents ou payments.');

            return self::FAILURE;
        }

        $result = $reminders->execute(only: is_string($only) ? $only : null);
        $this->info('Candidacy reminders: '.json_encode($result, JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
