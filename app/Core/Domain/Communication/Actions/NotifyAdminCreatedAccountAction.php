<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Application\Mail\Jobs\SendAdminCreatedAccountMailJob;
use App\Core\Domain\Identity\Models\User;

final class NotifyAdminCreatedAccountAction
{
    public function execute(User $user, string $plainPassword): void
    {
        if (! $user->canReceiveEmail()) {
            return;
        }

        SendAdminCreatedAccountMailJob::dispatch($user->id, $plainPassword)
            ->onQueue((string) config('queue.mail_queue', 'mail'));
    }
}
