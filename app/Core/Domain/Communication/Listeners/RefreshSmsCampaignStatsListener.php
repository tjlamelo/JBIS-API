<?php

namespace App\Core\Domain\Communication\Listeners;

use App\Core\Domain\Communication\Actions\RefreshSmsCampaignStatsAction;
use App\Core\Domain\Communication\Events\SmsCampaignDispatched;
use App\Core\Domain\Communication\Models\SmsCampaign;

class RefreshSmsCampaignStatsListener
{
    public function __construct(
        private readonly RefreshSmsCampaignStatsAction $refreshSmsCampaignStatsAction,
    ) {}

    public function handle(SmsCampaignDispatched $event): void
    {
        $campaign = SmsCampaign::query()->find($event->campaignId);
        if (! $campaign) {
            return;
        }

        $this->refreshSmsCampaignStatsAction->execute($campaign);
    }
}
