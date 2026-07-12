<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Jobs;

use App\Core\Application\Mail\Mailable\OperationsAlertMail;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendOperationsAlertMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly string $subject,
        public readonly string $body,
        public readonly string $actionUrl,
    ) {}

    public function handle(): void
    {
        $user = User::query()->find($this->userId);
        if ($user === null || ! $user->canReceiveEmail()) {
            return;
        }

        Mail::to($user->email)->send(new OperationsAlertMail(
            user: $user,
            alertSubject: $this->subject,
            alertBody: $this->body,
            actionUrl: $this->actionUrl,
        ));
    }
}
