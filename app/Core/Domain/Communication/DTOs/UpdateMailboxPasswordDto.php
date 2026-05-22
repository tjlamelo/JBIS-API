<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class UpdateMailboxPasswordDto
{
    public function __construct(
        public string $localPart,
        public string $password,
    ) {}
}
