<?php

namespace App\Core\Domain\Communication\Events;

class SmsCampaignDispatched
{
    public function __construct(
        public readonly int $campaignId,
    ) {}
}
