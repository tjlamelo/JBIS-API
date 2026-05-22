<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class MailboxCreationResultDto
{
    public function __construct(
        public bool $success,
        public string $email,
        public string $message,
        public ?string $rawError = null,
    ) {}
}
