<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\DTOs;

/**
 * Requête de génération de texte, indépendante du fournisseur.
 *
 * @param  list<ChatMessage>  $messages
 */
final readonly class GenerateContentRequest
{
    /**
     * @param  list<ChatMessage>  $messages
     */
    public function __construct(
        public array $messages,
        public ?GenerationOptions $options = null,
    ) {
        if ($messages === []) {
            throw new \InvalidArgumentException('Au moins un message est requis.');
        }
    }
}
