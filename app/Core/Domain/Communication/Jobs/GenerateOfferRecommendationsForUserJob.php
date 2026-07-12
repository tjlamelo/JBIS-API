<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Jobs;

use App\Core\Domain\Communication\Actions\DispatchOfferRecommendationsAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class GenerateOfferRecommendationsForUserJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $userId,
    ) {
        $this->onQueue((string) config('queue.mail_queue', 'default'));
    }

    public function uniqueId(): string
    {
        return 'offer-reco:'.$this->userId.':'.now('Africa/Douala')->format('o-W');
    }

    public function handle(DispatchOfferRecommendationsAction $action): void
    {
        $action->recommendForUserId($this->userId);
    }
}
