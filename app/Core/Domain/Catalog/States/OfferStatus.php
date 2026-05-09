<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\States;

enum OfferStatus: string
{
    case Draft = 'DRAFT';
    case Published = 'PUBLISHED';
    case Archived = 'ARCHIVED';
    case Closed = 'CLOSED';
}
