<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class RenderedMailDto
{
    public function __construct(
        public string $subject,
        public ?string $body,
        public ?MailTemplateContentDto $content,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function contentArray(): ?array
    {
        return $this->content?->toArray();
    }
}
