<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class MailCampaignDto
{
    /**
     * @param array<string, mixed> $targeting
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $subject,
        public ?string $body,
        public ?array $content,
        public array $targeting,
        public string $sendMode = 'queue',
        public ?string $name = null,
        public ?string $fromName = null,
        public ?string $replyTo = null,
        public array $meta = [],
    ) {}
}
