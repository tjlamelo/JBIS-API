<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class DeleteMailboxDto
{
    public function __construct(
        public string $localPart,
    ) {}
}
