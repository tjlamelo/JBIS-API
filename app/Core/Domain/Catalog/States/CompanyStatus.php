<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\States;

enum CompanyStatus: string
{
    case Draft = 'DRAFT';
    case Published = 'PUBLISHED';
    case Archived = 'ARCHIVED';
}
