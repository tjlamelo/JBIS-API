<?php

namespace App\Core\Domain\Communication\Listeners;

use App\Core\Domain\Communication\Actions\RefreshMailCampaignStatsAction;
use App\Core\Domain\Communication\Events\MailCampaignDispatched;
use App\Core\Domain\Communication\Models\MailCampaign;

class RefreshMailCampaignStatsListener
{
    public function __construct(
        private readonly RefreshMailCampaignStatsAction $refreshMailCampaignStatsAction,
    ) {}

    public function handle(MailCampaignDispatched $event): void
    {
        $campaign = MailCampaign::query()->find($event->campaignId);
        if (! $campaign) {
            return;
        }

        $this->refreshMailCampaignStatsAction->execute($campaign);
    }
}
