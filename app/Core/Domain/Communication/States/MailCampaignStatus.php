<?php

namespace App\Core\Domain\Communication\States;

enum MailCampaignStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Queued = 'queued';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Processing, self::Queued, self::Failed],
            self::Processing => [self::Queued, self::Completed, self::Failed],
            self::Queued => [self::Processing, self::Completed, self::Failed],
            self::Completed => [],
            self::Failed => [self::Processing, self::Queued],
        };
    }
}
