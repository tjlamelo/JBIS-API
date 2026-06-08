<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Enums;

enum RecruiterOnboardingStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case NeedsChanges = 'needs_changes';
}
