<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Jobs;

use App\Core\Application\Mail\Mailable\WelcomePlatformMail;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envoi asynchrone du mail de bienvenue (1 hop queue → SMTP).
 */
final class SendWelcomePlatformMailJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $timeout = 60;

    /** @var list<int> */
    public array $backoff = [15, 60, 180, 600];

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $userId,
    ) {
        $this->onQueue((string) config('queue.mail_queue', 'mail'));
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return 'welcome-mail:'.$this->userId;
    }

    public function handle(): void
    {
        $user = User::query()->find($this->userId);
        if ($user === null || ! $user->canReceiveEmail()) {
            return;
        }

        Mail::to($user->email)->send(new WelcomePlatformMail($user));

        Log::info('welcome_email_sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'attempt' => $this->attempts(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('welcome_email_failed_permanently', [
            'user_id' => $this->userId,
            'attempt' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }
}
