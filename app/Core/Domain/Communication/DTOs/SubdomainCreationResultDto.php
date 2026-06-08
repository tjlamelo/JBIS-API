<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\DTOs;

final readonly class SubdomainCreationResultDto
{
    public function __construct(
        public bool $success,
        public string $fqdn,
        public string $message,
        public ?string $rawError = null,
    ) {}
}
