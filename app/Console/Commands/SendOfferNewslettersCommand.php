<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Domain\Communication\Actions\DispatchOfferNewslettersAction;
use Illuminate\Console\Command;

final class SendOfferNewslettersCommand extends Command
{
    protected $signature = 'newsletter:send-offers {--limit= : Nombre max d’abonnés}';

    protected $description = 'Envoie la newsletter d’offres aux abonnés actifs';

    public function handle(DispatchOfferNewslettersAction $action): int
    {
        $limitRaw = $this->option('limit');
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : null;

        $stats = $action->execute($limit);

        $this->info(sprintf(
            'Newsletter offres — sent:%d skipped:%d total:%d',
            $stats['sent'],
            $stats['skipped'],
            $stats['total'],
        ));

        return self::SUCCESS;
    }
}
