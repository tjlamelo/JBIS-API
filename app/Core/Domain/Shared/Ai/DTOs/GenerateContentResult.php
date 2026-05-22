<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\DTOs;

/**
 * Réponse normalisée après génération.
 */
final readonly class GenerateContentResult
{
    /**
     * @param  array<string, mixed>|null  $raw  Métadonnées brutes (usage, finishReason, etc.) pour debug ou analytics.
     */
    public function __construct(
        public string $text,
        public ?string $finishReason = null,
        public ?array $raw = null,
    ) {}
}
