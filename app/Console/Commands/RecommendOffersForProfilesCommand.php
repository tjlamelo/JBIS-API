<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Domain\Communication\Actions\DispatchOfferRecommendationsAction;
use Illuminate\Console\Command;

final class RecommendOffersForProfilesCommand extends Command
{
    protected $signature = 'offers:recommend-profiles
        {--limit= : Nombre max de candidats}
        {--sync : Exécuter inline (sans file)}';

    protected $description = 'Génère des recommandations d’offres IA par profil candidat et notifie en interne';

    public function handle(DispatchOfferRecommendationsAction $action): int
    {
        $limitRaw = $this->option('limit');
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : null;
        $sync = (bool) $this->option('sync');

        $stats = $action->execute($limit, $sync);

        $this->info(sprintf(
            'Reco offres — queued:%d processed:%d notified:%d skipped:%d failed:%d',
            $stats['queued'],
            $stats['processed'],
            $stats['notified'],
            $stats['skipped'],
            $stats['failed'],
        ));

        return self::SUCCESS;
    }
}
