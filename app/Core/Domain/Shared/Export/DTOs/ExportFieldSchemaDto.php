<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\DTOs;

use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

/**
 * Catalogue d'un champ exportable côté serveur.
 *
 * Permet à l'UI/admin de découvrir dynamiquement (via /exports/schema)
 * les champs disponibles pour chaque source.
 */
final readonly class ExportFieldSchemaDto
{
    public function __construct(
        public string $key,
        public string $label,
        public string $path,
        public ExportFieldType $type = ExportFieldType::String,
        public ?string $format = null,
        public mixed $default = null,
        public ?string $group = null,
        public ?string $description = null,
        /** Relations Eloquent nécessaires si ce champ est demandé. */
        public array $requiresWith = [],
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'path' => $this->path,
            'type' => $this->type->value,
            'format' => $this->format,
            'default' => $this->default,
            'group' => $this->group,
            'description' => $this->description,
            'requires_with' => $this->requiresWith,
        ];
    }
}
