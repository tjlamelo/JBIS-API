<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Jobs;

use App\Core\Domain\Identity\Actions\Document\ProcessUserDocumentExtractionAction;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelRateLimitedException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ExtractUserDocumentJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $timeout = 180;

    public int $uniqueFor = 300;

    /** @var list<int> */
    public array $backoff = [30, 60, 120, 180, 300];

    public function __construct(
        public readonly int $userDocumentId,
    ) {
        $this->onQueue((string) config('ai.document_extraction.queue', 'default'));
    }

    public function uniqueId(): string
    {
        return 'extract-user-document-'.$this->userDocumentId;
    }

    public function handle(ProcessUserDocumentExtractionAction $processor): void
    {
        Log::info('[document_extraction] Job démarré', [
            'user_document_id' => $this->userDocumentId,
            'job_id' => $this->job?->getJobId(),
            'attempt' => $this->attempts(),
        ]);

        if (! (bool) config('ai.document_extraction.enabled', true)) {
            Log::info('[document_extraction] Job ignoré (extraction désactivée)', [
                'user_document_id' => $this->userDocumentId,
            ]);

            return;
        }

        try {
            $extraction = $processor->execute($this->userDocumentId);
        } catch (LanguageModelRateLimitedException $exception) {
            $delay = max(30, $exception->retryAfterSeconds ?? 60);
            Log::warning('[document_extraction] Job release (rate limit)', [
                'user_document_id' => $this->userDocumentId,
                'attempt' => $this->attempts(),
                'release_after' => $delay,
            ]);
            $this->release($delay);

            return;
        }

        Log::info('[document_extraction] Job terminé', [
            'user_document_id' => $this->userDocumentId,
            'extraction_id' => $extraction?->id,
            'status' => $extraction?->status?->value,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[document_extraction] Job échoué définitivement', [
            'user_document_id' => $this->userDocumentId,
            'attempt' => $this->attempts(),
            'message' => $exception->getMessage(),
        ]);
    }
}
