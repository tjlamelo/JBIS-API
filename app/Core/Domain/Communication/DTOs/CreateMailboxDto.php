<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class CreateMailboxDto
{
    public function __construct(
        public string $localPart,
        public string $password,
        public ?int $quotaMb = null,
    ) {}
}
