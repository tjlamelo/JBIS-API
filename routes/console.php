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

Artisan::command('mail:welcome {user : Email ou ID utilisateur} {--sync : Envoi immédiat (sans queue)}', function () {
    $raw = (string) $this->argument('user');
    $user = is_numeric($raw)
        ? \App\Core\Domain\Identity\Models\User::query()->find((int) $raw)
        : \App\Core\Domain\Identity\Models\User::query()->where('email', $raw)->first();

    if ($user === null) {
        $this->error('Utilisateur introuvable.');

        return 1;
    }

    if ($this->option('sync')) {
        \Illuminate\Support\Facades\Mail::to($user->email)->send(
            new \App\Core\Application\Mail\Mailable\WelcomePlatformMail($user)
        );
        $this->info("Welcome envoyé en sync à {$user->email}");

        return 0;
    }

    \App\Core\Application\Mail\Jobs\SendWelcomePlatformMailJob::dispatch($user->id);
    $this->info("Welcome job dispatché pour {$user->email} (queue=".config('queue.mail_queue', 'default').')');

    return 0;
})->purpose('Renvoyer / tester le mail de bienvenue JBIS');
