<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Jobs;

use App\Core\Application\Mail\Mailable\StaffWelcomeMail;
use App\Core\Domain\Communication\Support\LocalizedCopy;
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

final class SendStaffWelcomeMailJob implements ShouldBeUnique, ShouldQueue
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
        return 'staff-welcome-mail:'.$this->userId;
    }

    public function handle(): void
    {
        $user = User::query()->with(['profile', 'settings'])->find($this->userId);
        if ($user === null || ! $user->canReceiveEmail()) {
            return;
        }

        $locale = LocalizedCopy::userLocale($user);
        Mail::to($user->email)->send(new StaffWelcomeMail($user, $locale));

        Log::info('staff_welcome_email_sent', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('staff_welcome_email_failed', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
