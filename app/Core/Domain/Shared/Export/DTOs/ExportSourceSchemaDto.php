<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\DTOs;

/**
 * Description d'une source exportable, renvoyée par l'API de schéma.
 */
final readonly class ExportSourceSchemaDto
{
    /**
     * @param  array<int, ExportFieldSchemaDto>  $fields
     */
    public function __construct(
        public string $key,
        public string $label,
        public array $fields,
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'fields' => array_map(static fn (ExportFieldSchemaDto $f) => $f->toArray(), $this->fields),
        ];
    }
}
