<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class SmsDispatchResultDto
{
    public function __construct(
        public string $status,
        public ?string $providerMessageId = null,
        public ?string $errorMessage = null,
    ) {}
}
