<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Domain\Communication\Actions\DispatchOfferNewslettersAction;
use Illuminate\Console\Command;

final class SendOfferNewslettersCommand extends Command
{
    protected $signature = 'newsletter:send-offers
                            {--limit= : Nombre max d’abonnés}
                            {--force : Ignorer last_sent_at de la semaine}';

    protected $description = 'Enfile la newsletter d’offres pour les abonnés actifs (file mail)';

    public function handle(DispatchOfferNewslettersAction $action): int
    {
        $limitRaw = $this->option('limit');
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : null;

        $stats = $action->execute($limit, (bool) $this->option('force'));

        $this->info(sprintf(
            'Newsletter offres — queued:%d skipped:%d total:%d batch:%s',
            $stats['queued'],
            $stats['skipped'],
            $stats['total'],
            $stats['batch'],
        ));

        return self::SUCCESS;
    }
}
