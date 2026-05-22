<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\DTOs;

/**
 * Paramètres optionnels de génération (généralement mappés vers l'API du fournisseur).
 */
final readonly class GenerationOptions
{
    /**
     * @param  array<string, mixed>|null  $responseSchema  Schéma JSON (format Gemini : types en majuscules, ex. STRING, OBJECT).
     */
    public function __construct(
        public ?float $temperature = null,
        public ?int $maxOutputTokens = null,
        public ?string $responseMimeType = null,
        public ?array $responseSchema = null,
    ) {}

    public function wantsStructuredJson(): bool
    {
        return $this->responseMimeType === 'application/json'
            && $this->responseSchema !== null
            && $this->responseSchema !== [];
    }
}
