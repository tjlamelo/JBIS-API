<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class UpdateMailboxQuotaDto
{
    public function __construct(
        public string $localPart,
        public int $quotaMb,
    ) {}
}
