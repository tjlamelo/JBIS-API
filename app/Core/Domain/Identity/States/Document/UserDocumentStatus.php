<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\States\Document;

enum UserDocumentStatus: string
{
    case Pending = 'PENDING';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Expired = 'EXPIRED';
}
