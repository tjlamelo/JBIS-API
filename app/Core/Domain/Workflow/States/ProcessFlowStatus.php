<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\States;

enum ProcessFlowStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
