<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Enums;

enum PartnerCohortStudentDocumentStatus: string
{
    case Missing = 'missing';
    case Uploaded = 'uploaded';
    case Validated = 'validated';
    case Rejected = 'rejected';
}
