<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs\Skill;

readonly class SkillDto
{
    /**
     * @param  list<string>  $provided_keys
     * @param  array<string, string>  $name
     */
    public function __construct(
        public array $provided_keys,
        public array $name,
        public string $slug,
        public ?int $skill_category_id = null,
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
            skill_category_id: array_key_exists('skill_category_id', $data)
                ? (($data['skill_category_id'] ?? null) !== null && $data['skill_category_id'] !== ''
                    ? (int) $data['skill_category_id']
                    : null)
                : null,
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
