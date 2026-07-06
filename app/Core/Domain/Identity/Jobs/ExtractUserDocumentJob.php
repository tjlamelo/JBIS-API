<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Jobs;

use App\Core\Domain\Identity\Actions\Document\ProcessUserDocumentExtractionAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ExtractUserDocumentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    /** @var list<int> */
    public array $backoff = [30, 120];

    public function __construct(
        public readonly int $userDocumentId,
    ) {}

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

        $extraction = $processor->execute($this->userDocumentId);

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
