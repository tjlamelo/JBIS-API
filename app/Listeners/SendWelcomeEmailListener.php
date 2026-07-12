<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Core\Application\Mail\Jobs\SendWelcomePlatformMailJob;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

/**
 * Dispatch rapide du job welcome (ne bloque pas l'inscription).
 */
final class SendWelcomeEmailListener
{
    public function handle(Registered $event): void
    {
        $user = $event->user;
        if (! $user instanceof User || ! $user->email) {
            return;
        }

        SendWelcomePlatformMailJob::dispatch($user->id);

        Log::info('welcome_email_dispatched', [
            'user_id' => $user->id,
            'email' => $user->email,
            'queue' => config('queue.mail_queue', 'default'),
        ]);
    }
}
