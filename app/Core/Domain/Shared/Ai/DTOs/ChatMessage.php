<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\DTOs;

use App\Core\Domain\Shared\Ai\Enums\ChatRole;

/**
 * Un message dans une conversation envoyée au modèle.
 */
final readonly class ChatMessage
{
    public function __construct(
        public ChatRole $role,
        public string $content,
    ) {}
}
