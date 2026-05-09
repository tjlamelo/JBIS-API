<?php

namespace App\Core\Domain\Identity\DTOs;

final readonly class LoginCredentialsDto
{
    public function __construct(
        public string $login,
        public string $password,
        public string $deviceName = 'api',
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            login: (string) ($payload['login'] ?? $payload['email'] ?? ''),
            password: (string) ($payload['password'] ?? ''),
            deviceName: (string) ($payload['device_name'] ?? 'api'),
        );
    }
}
