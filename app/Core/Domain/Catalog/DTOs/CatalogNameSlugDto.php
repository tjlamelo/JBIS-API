<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs;

/**
 * DTO partagé pour les référentiels catalogue : nom multilingue (JSON) + slug string unique.
 *
 * @param  list<string>  $provided_keys
 * @param  array<string, string>  $name
 */
readonly class CatalogNameSlugDto
{
    public function __construct(
        public array $provided_keys,
        public array $name,
        public string $slug,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = $data['provided_keys'] ?? array_keys($data);
        $name = is_array($data['name'] ?? null) ? $data['name'] : [];

        return new self(
            provided_keys: array_values($providedKeys),
            name: $name,
            slug: (string) ($data['slug'] ?? ''),
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
