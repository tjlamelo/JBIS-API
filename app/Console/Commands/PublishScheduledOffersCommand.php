<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Domain\Catalog\Actions\Offer\PublishScheduledOffersAction;
use Illuminate\Console\Command;

final class PublishScheduledOffersCommand extends Command
{
    protected $signature = 'offers:publish-scheduled';

    protected $description = 'Passe en PUBLISHED les offres brouillon dont published_at est atteint';

    public function handle(PublishScheduledOffersAction $action): int
    {
        $result = $action->execute();
        $this->info(sprintf(
            'Publications planifiées : %d offre(s) — ids=[%s]',
            $result['published'],
            implode(',', $result['ids']),
        ));

        return self::SUCCESS;
    }
}
