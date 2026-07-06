<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\DTOs;

use App\Core\Domain\Shared\Ai\Enums\ChatRole;

/**
 * Un message dans une conversation envoyée au modèle.
 */
final readonly class ChatMessage
{
    /**
     * @param  list<string>  $imageUrls
     */
    public function __construct(
        public ChatRole $role,
        public string $content,
        /** @deprecated Préférer {@see $imageUrls} */
        public ?string $imageUrl = null,
        public array $imageUrls = [],
    ) {}

    /**
     * @return list<string>
     */
    public function allImageUrls(): array
    {
        if ($this->imageUrls !== []) {
            return $this->imageUrls;
        }

        if ($this->imageUrl !== null && $this->imageUrl !== '') {
            return [$this->imageUrl];
        }

        return [];
    }
}
