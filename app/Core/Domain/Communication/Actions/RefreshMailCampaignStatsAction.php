<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Models\MailCampaign;
use App\Core\Domain\Communication\States\MailCampaignStatus;

class RefreshMailCampaignStatsAction
{
    public function execute(MailCampaign $campaign): MailCampaign
    {
        $sentCount = $campaign->dispatches()->where('status', 'sent')->count();
        $failedCount = $campaign->dispatches()->where('status', 'failed')->count();
        $pendingCount = $campaign->dispatches()->where('status', 'pending')->count();

        $campaign->update([
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'sent_at' => $pendingCount > 0 ? null : now(),
        ]);

        $campaign->transitionTo($pendingCount > 0 ? MailCampaignStatus::Processing : MailCampaignStatus::Completed);

        return $campaign->fresh(['dispatches']) ?? $campaign;
    }
}
