<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Enums;

enum DocumentExtractionStatus: string
{
    case Processing = 'processing';
    case PendingReview = 'pending_review';
    case Applied = 'applied';
    case Rejected = 'rejected';
    case Failed = 'failed';
}
