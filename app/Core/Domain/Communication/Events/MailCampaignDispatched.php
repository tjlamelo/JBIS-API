<?php

namespace App\Core\Domain\Communication\Events;

class MailCampaignDispatched
{
    public function __construct(
        public readonly int $campaignId,
    ) {}
}
