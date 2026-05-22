<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\DTOs;

use App\Core\Domain\Shared\Export\Enums\ExportFormat;

/**
 * Définition complète d'un export :
 *  - format de sortie (xlsx, csv, pdf)
 *  - nom de fichier souhaité (sans extension)
 *  - liste des feuilles (mono ou multi)
 *  - méta libres (titre, sous-titre, template PDF, options driver…)
 */
final readonly class ExportDefinitionDto
{
    /**
     * @param  array<int, ExportSheetDto>  $sheets
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public ExportFormat $format,
        public string $fileName,
        public array $sheets,
        public array $meta = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $sheetsData = array_values((array) ($data['sheets'] ?? []));
        $sheets = array_map(static fn (array $sheet) => ExportSheetDto::fromArray($sheet), $sheetsData);

        return new self(
            format: ExportFormat::fromLoose((string) ($data['format'] ?? 'xlsx')),
            fileName: (string) ($data['file_name'] ?? 'export'),
            sheets: $sheets,
            meta: (array) ($data['meta'] ?? []),
        );
    }
}
