<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class SmsCampaignDto
{
    /**
     * @param array<string, mixed> $targeting
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $message,
        public array $targeting,
        public string $sendMode = 'queue',
        public ?string $senderId = null,
        public ?string $name = null,
        public array $meta = [],
    ) {}
}
