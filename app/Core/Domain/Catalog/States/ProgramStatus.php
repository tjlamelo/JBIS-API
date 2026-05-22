<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\States;

enum ProgramStatus: string
{
    case Draft = 'DRAFT';

    case Published = 'PUBLISHED';

    case Archived = 'ARCHIVED';

    case Expired = 'EXPIRED';
}
