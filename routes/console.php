<?php

use App\Core\Domain\Analytics\Jobs\SyncGa4AnalyticsJob;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('analytics:sync-ga4 {--date= : Date to sync (YYYY-MM-DD). Defaults to yesterday.} {--sync : Run inline instead of queueing.}', function () {
    $date = (string) ($this->option('date') ?: CarbonImmutable::yesterday()->toDateString());
    $runSync = (bool) $this->option('sync');

    if ($runSync) {
        SyncGa4AnalyticsJob::dispatchSync($date);
    } else {
        SyncGa4AnalyticsJob::dispatch($date);
    }

    $this->info('GA4 sync dispatched for date: '.$date);
})->purpose('Sync GA4 analytics and persist daily metrics to the database');

Artisan::command('newsletter:send-offers {--limit= : Max subscribers to process}', function () {
    $limit = $this->option('limit');
    $stats = app(\App\Core\Domain\Communication\Actions\DispatchOfferNewslettersAction::class)
        ->execute($limit !== null && $limit !== '' ? (int) $limit : null);

    $this->info(sprintf(
        'Newsletter offres : %d envoyée(s), %d ignorée(s), sur %d abonné(s).',
        $stats['sent'],
        $stats['skipped'],
        $stats['total'],
    ));
})->purpose('Envoyer la newsletter offres national/international aux abonnés actifs');
