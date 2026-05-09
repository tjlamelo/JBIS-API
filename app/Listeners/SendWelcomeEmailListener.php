<?php

namespace App\Listeners;

use App\Core\Application\Mail\Mailable\WelcomePlatformMail;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Registered $event): void
    {
        $user = $event->user;
        if (! $user instanceof User || ! $user->email) {
            return;
        }

        Mail::to($user->email)->queue(new WelcomePlatformMail($user));
    }
}
