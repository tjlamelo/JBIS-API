<?php

use App\Http\Middleware\ResolveRecruiterTenant;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', [
            SetApiLocale::class,
            ResolveRecruiterTenant::class,
        ]);

        $middleware->alias([
            'recruiter.tenant' => ResolveRecruiterTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // o2switch : traite la file chaque minute (cron schedule:run requis)
        $schedule->command('queue:work --queue=mail,default --stop-when-empty --max-time=50 --tries=5 --sleep=1')
            ->everyMinute()
            ->withoutOverlapping(4)
            ->name('queue-worker-tick');

        if (config('services.newsletter.schedule_enabled', true)) {
            $schedule->command('newsletter:send-offers')
                ->weeklyOn(1, '08:00')
                ->timezone('Africa/Douala')
                ->withoutOverlapping()
                ->onOneServer();
        }

        $schedule->command('notifications:dispatch --only=week_start')
            ->weeklyOn(1, '08:00')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('notifications:dispatch --only=weekend')
            ->weeklyOn(5, '18:00')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('notifications:dispatch --only=holidays')
            ->dailyAt('08:05')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('notifications:dispatch --only=birthdays')
            ->dailyAt('08:10')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('offers:recommend-profiles')
            ->weeklyOn(2, '09:00')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('operations:dispatch-notifications --only=task_reminders')
            ->dailyAt('09:30')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('operations:dispatch-notifications --only=weekly_recap')
            ->weeklyOn(6, '10:00')
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('offers:publish-scheduled')
            ->everyMinute()
            ->timezone('Africa/Douala')
            ->withoutOverlapping()
            ->onOneServer();
    })
    ->create();
