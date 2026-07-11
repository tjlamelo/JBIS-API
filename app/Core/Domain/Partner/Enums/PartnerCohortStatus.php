<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Enums;

enum PartnerCohortStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Active = 'active';
    case Closed = 'closed';
    case Rejected = 'rejected';
}
