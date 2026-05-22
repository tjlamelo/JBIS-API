<?php

declare(strict_types=1);

namespace App\Core\Domain\Analytics\Jobs;

use App\Core\Domain\Analytics\Services\SyncGa4AnalyticsAction;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncGa4AnalyticsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $date,
    ) {}

    public function handle(SyncGa4AnalyticsAction $action): void
    {
        $action->execute(CarbonImmutable::parse($this->date));
    }
}
