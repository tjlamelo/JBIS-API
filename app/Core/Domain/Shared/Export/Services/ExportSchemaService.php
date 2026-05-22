<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Services;

use App\Core\Domain\Shared\Export\DTOs\ExportSourceSchemaDto;
use App\Core\Domain\Shared\Export\Registry\ExportDriverRegistry;
use App\Core\Domain\Shared\Export\Registry\ExportSourceRegistry;

/**
 * Service de découverte (côté admin/UI) :
 *
 *  - liste des sources exportables avec leurs champs et leurs labels
 *  - liste des formats disponibles
 *
 * À utiliser pour construire dynamiquement un formulaire d'export
 * « cliquable » côté front (sélection des sources et des colonnes).
 */
final class ExportSchemaService
{
    public function __construct(
        private readonly ExportSourceRegistry $sources,
        private readonly ExportDriverRegistry $drivers,
    ) {}

    /**
     * @return array<int, ExportSourceSchemaDto>
     */
    public function sources(): array
    {
        $items = [];
        foreach ($this->sources->all() as $source) {
            $items[] = new ExportSourceSchemaDto(
                key: $source->key(),
                label: $source->label(),
                fields: array_values($source->fieldsSchema()),
            );
        }

        return $items;
    }

    /**
     * @return array<int, string>
     */
    public function formats(): array
    {
        return $this->drivers->availableFormats();
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'formats' => $this->formats(),
            'sources' => array_map(static fn (ExportSourceSchemaDto $s) => $s->toArray(), $this->sources()),
        ];
    }
}
