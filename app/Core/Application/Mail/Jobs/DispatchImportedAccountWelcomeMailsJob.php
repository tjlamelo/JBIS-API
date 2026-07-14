<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Répartit les mails de bienvenue d'un import en paquets pour éviter d'inonder SMTP.
 *
 * @phpstan-type PendingMail array{user_id: int, plain_password: string}
 */
final class DispatchImportedAccountWelcomeMailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @param  list<PendingMail>  $pendingMails
     */
    public function __construct(
        public readonly array $pendingMails,
        public readonly int $chunkSize = 40,
        public readonly int $delaySecondsBetweenChunks = 5,
    ) {
        $this->onQueue((string) config('queue.mail_queue', 'mail'));
        $this->afterCommit();
    }

    public function handle(): void
    {
        if ($this->pendingMails === []) {
            return;
        }

        $chunks = array_chunk($this->pendingMails, max(1, $this->chunkSize));

        foreach ($chunks as $chunkIndex => $chunk) {
            $delay = $chunkIndex * max(0, $this->delaySecondsBetweenChunks);

            foreach ($chunk as $item) {
                $userId = (int) ($item['user_id'] ?? 0);
                $password = (string) ($item['plain_password'] ?? '');
                if ($userId <= 0 || $password === '') {
                    continue;
                }

                SendAdminCreatedAccountMailJob::dispatch($userId, $password)
                    ->delay(now()->addSeconds($delay));
            }
        }

        Log::info('imported_account_welcome_mails_dispatched', [
            'total' => count($this->pendingMails),
            'chunks' => count($chunks),
            'chunk_size' => $this->chunkSize,
        ]);
    }
}
