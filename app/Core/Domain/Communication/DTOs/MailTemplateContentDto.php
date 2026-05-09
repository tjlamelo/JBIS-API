<?php

namespace App\Core\Domain\Communication\DTOs;

final readonly class MailTemplateContentDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public array $payload,
    ) {}

    /**
     * @param array<string, mixed>|null $payload
     */
    public static function fromNullableArray(?array $payload): ?self
    {
        if ($payload === null) {
            return null;
        }

        return new self($payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function variables(): array
    {
        $variables = $this->payload['variables'] ?? [];

        return is_array($variables) ? $variables : [];
    }
}
