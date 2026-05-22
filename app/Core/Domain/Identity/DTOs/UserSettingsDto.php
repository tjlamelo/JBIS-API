<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\DTOs;

readonly class UserSettingsDto
{
    /**
     * @param  list<string>  $provided_keys
     * @param  array<string, mixed>|null  $notifications
     * @param  array<string, mixed>|null  $privacy
     * @param  array<string, mixed>|null  $marketing
     */
    public function __construct(
        public array $provided_keys = [],
        public ?string $language = null,
        public ?string $theme = null,
        public ?string $timezone = null,
        public ?array $notifications = null,
        public ?array $privacy = null,
        public ?array $marketing = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            provided_keys: array_values($data['provided_keys'] ?? array_keys($data)),
            language: isset($data['language']) ? (string) $data['language'] : null,
            theme: isset($data['theme']) ? (string) $data['theme'] : null,
            timezone: isset($data['timezone']) ? (string) $data['timezone'] : null,
            notifications: isset($data['notifications']) && is_array($data['notifications']) ? $data['notifications'] : null,
            privacy: isset($data['privacy']) && is_array($data['privacy']) ? $data['privacy'] : null,
            marketing: isset($data['marketing']) && is_array($data['marketing']) ? $data['marketing'] : null,
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
