<?php

namespace App\Core\Domain\Identity\DTOs;

use App\Core\Domain\Identity\Models\User;

final readonly class AuthenticationResultDto
{
    public function __construct(
        public User $user,
        public string $accessToken,
        public string $tokenType = 'Bearer',
    ) {}

    /**
     * @return array{user: User, access_token: string, token_type: string}
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
        ];
    }
}
