<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Jobs;

use App\Core\Application\Mail\Mailable\CameroonHolidayMail;
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

final class SendCameroonHolidayMailJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $timeout = 60;

    /** @var list<int> */
    public array $backoff = [15, 60, 180, 600];

    public int $uniqueFor = 86_400;

    public function __construct(
        public readonly int $userId,
        public readonly string $holidayTitle,
        public readonly string $holidayBody,
        public readonly string $holidayCode,
        public readonly string $holidayDate,
        public readonly string $locale = 'fr',
    ) {
        $this->onQueue((string) config('queue.mail_queue', 'default'));
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return sprintf('holiday-mail:%d:%s:%s', $this->userId, $this->holidayCode, $this->holidayDate);
    }

    public function handle(): void
    {
        $user = User::query()->with(['profile', 'settings'])->find($this->userId);
        if ($user === null || ! $user->email) {
            return;
        }

        Mail::to($user->email)->send(new CameroonHolidayMail(
            $user,
            $this->holidayTitle,
            $this->holidayBody,
            $this->holidayCode,
            $this->holidayDate,
            $this->locale,
        ));

        Log::info('cameroon_holiday_email_sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'holiday_code' => $this->holidayCode,
            'holiday_date' => $this->holidayDate,
            'locale' => $this->locale,
            'attempt' => $this->attempts(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('cameroon_holiday_email_failed_permanently', [
            'user_id' => $this->userId,
            'holiday_code' => $this->holidayCode,
            'holiday_date' => $this->holidayDate,
            'attempt' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }
}
