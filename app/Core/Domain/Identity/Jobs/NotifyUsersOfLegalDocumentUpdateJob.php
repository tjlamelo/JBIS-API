<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Jobs;

use App\Core\Application\Mail\Mailable\LegalDocumentUpdatedMail;
use App\Core\Domain\Identity\Models\LegalDocument;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class NotifyUsersOfLegalDocumentUpdateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300, 900];

    /**
     * @param  list<int>  $userIds
     */
    public function __construct(
        public readonly int $legalDocumentId,
        public readonly array $userIds,
    ) {}

    public function handle(): void
    {
        $document = LegalDocument::query()->find($this->legalDocumentId);
        if ($document === null) {
            return;
        }

        $users = User::query()
            ->whereIn('id', $this->userIds)
            ->where('active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->queue(new LegalDocumentUpdatedMail($user, $document));
            } catch (\Throwable $exception) {
                Log::warning('Legal document update email failed', [
                    'legal_document_id' => $this->legalDocumentId,
                    'user_id' => $user->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
