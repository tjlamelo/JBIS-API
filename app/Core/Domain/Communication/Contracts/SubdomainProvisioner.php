<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Contracts;

use App\Core\Domain\Communication\DTOs\SubdomainCreationResultDto;

interface SubdomainProvisioner
{
    public function createSubdomain(string $subdomain, string $rootDomain, string $documentRoot): SubdomainCreationResultDto;
}
