<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\DTOs;

/**
 * Représente une « feuille » de l'export (un onglet Excel,
 * un fichier CSV dans un ZIP, ou une section PDF).
 *
 *  - $source : clé d'une ExportSourceInterface enregistrée
 *  - $fields : colonnes choisies par l'utilisateur (ordre conservé)
 *  - $filters : filtres passés à la baseQuery de la source
 *  - $with   : relations Eloquent supplémentaires à charger (en plus
 *              de defaultWith() de la source)
 *  - $chunkSize : taille de lot pour le streaming (0 = pas de chunk)
 */
final readonly class ExportSheetDto
{
    /**
     * @param  array<int, ExportFieldDto>  $fields
     * @param  array<string,mixed>  $filters
     * @param  array<int, string>  $with
     */
    public function __construct(
        public string $name,
        public string $source,
        public array $fields,
        public array $filters = [],
        public array $with = [],
        public int $chunkSize = 500,
    ) {}

    /**
     * @param  array<string,mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $fields = array_map(
            static fn (array $field) => ExportFieldDto::fromArray($field),
            array_values((array) ($data['fields'] ?? []))
        );

        return new self(
            name: (string) ($data['name'] ?? 'Sheet1'),
            source: (string) ($data['source'] ?? ''),
            fields: $fields,
            filters: (array) ($data['filters'] ?? []),
            with: array_values(array_filter(
                (array) ($data['with'] ?? []),
                static fn ($v) => is_string($v) && $v !== ''
            )),
            chunkSize: (int) ($data['chunk_size'] ?? 500),
        );
    }
}
