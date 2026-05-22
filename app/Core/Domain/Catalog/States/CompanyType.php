<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\States;

enum CompanyType: string
{
    case Employer = 'EMPLOYER';
    case Partner = 'PARTNER';
    case School = 'SCHOOL';
}
