<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Enums;

enum RecruiterProfileRequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Matched = 'matched';
    case Transmitted = 'transmitted';
    case Rejected = 'rejected';
    case NeedsChanges = 'needs_changes';
}
