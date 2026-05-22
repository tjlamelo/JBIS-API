<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\DTOs;

use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

/**
 * Définit un champ (colonne) à inclure dans l'export.
 *
 * Le champ est résolu :
 *  - par `path` si fourni (dot-path : "profile.first_name", "documents", …)
 *  - sinon par `key` (qui est aussi utilisé comme identifiant en sortie)
 *
 * `type` pilote le formatage de la valeur (date, count, translatable, …).
 * `format` est utilisé selon le type (ex. format de date "d/m/Y H:i").
 * `default` est renvoyé lorsque la résolution renvoie null.
 */
final readonly class ExportFieldDto
{
    public function __construct(
        public string $key,
        public string $label,
        public ?string $path = null,
        public ExportFieldType $type = ExportFieldType::String,
        public ?string $format = null,
        public mixed $default = null,
        /** Locale forcée pour les champs translatables ; null = locale courante */
        public ?string $locale = null,
    ) {}

    public function resolvePath(): string
    {
        return $this->path ?? $this->key;
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $key = (string) ($data['key'] ?? '');

        return new self(
            key: $key,
            label: (string) ($data['label'] ?? $key),
            path: isset($data['path']) ? (string) $data['path'] : null,
            type: ExportFieldType::fromLoose(isset($data['type']) ? (string) $data['type'] : null),
            format: isset($data['format']) ? (string) $data['format'] : null,
            default: $data['default'] ?? null,
            locale: isset($data['locale']) ? (string) $data['locale'] : null,
        );
    }

    /**
     * Hydrate à partir d'un schéma serveur (ExportFieldSchemaDto) + overrides client.
     *
     * @param  array<string,mixed>  $override
     */
    public static function fromSchema(ExportFieldSchemaDto $schema, array $override = []): self
    {
        return new self(
            key: $schema->key,
            label: (string) ($override['label'] ?? $schema->label),
            path: (string) ($override['path'] ?? $schema->path),
            type: isset($override['type'])
                ? ExportFieldType::fromLoose((string) $override['type'])
                : $schema->type,
            format: isset($override['format']) ? (string) $override['format'] : $schema->format,
            default: $override['default'] ?? $schema->default,
            locale: isset($override['locale']) ? (string) $override['locale'] : null,
        );
    }
}
