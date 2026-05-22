<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Services;

use App\Core\Domain\Shared\Export\Contracts\ExportSourceInterface;
use App\Core\Domain\Shared\Export\Contracts\ResolvedSheet;
use App\Core\Domain\Shared\Export\DTOs\ExportDefinitionDto;
use App\Core\Domain\Shared\Export\DTOs\ExportFieldDto;
use App\Core\Domain\Shared\Export\DTOs\ExportResultDto;
use App\Core\Domain\Shared\Export\DTOs\ExportSheetDto;
use App\Core\Domain\Shared\Export\Exceptions\ExportException;
use App\Core\Domain\Shared\Export\Exceptions\InvalidFieldException;
use App\Core\Domain\Shared\Export\Registry\ExportDriverRegistry;
use App\Core\Domain\Shared\Export\Registry\ExportSourceRegistry;
use App\Core\Domain\Shared\Export\Support\FieldPathResolver;
use App\Core\Domain\Shared\Export\Support\ValueFormatter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Orchestrateur principal du module Export.
 *
 *  1. Valide la définition (sources connues, champs autorisés).
 *  2. Résout chaque feuille en streaming (chunk d'Eloquent) → ResolvedSheet.
 *  3. Délègue l'écriture au driver correspondant au format demandé.
 *
 * Le service ne tient aucun état entre deux appels : il est sûr d'instancier
 * une seule fois et de l'utiliser dans plusieurs requêtes.
 */
final class ExportService
{
    public function __construct(
        private readonly ExportSourceRegistry $sources,
        private readonly ExportDriverRegistry $drivers,
        private readonly FieldPathResolver $paths,
        private readonly ValueFormatter $formatter,
    ) {}

    public function export(ExportDefinitionDto $definition): ExportResultDto
    {
        if ($definition->sheets === []) {
            throw new ExportException('Aucune feuille à exporter : le tableau « sheets » est vide.');
        }

        $driver = $this->drivers->for($definition->format);

        $resolved = [];
        foreach ($definition->sheets as $sheet) {
            $resolved[] = $this->resolveSheet($sheet);
        }

        return $driver->export($definition, $resolved);
    }

    private function resolveSheet(ExportSheetDto $sheet): ResolvedSheet
    {
        $source = $this->sources->get($sheet->source);
        $fields = $this->prepareFields($source, $sheet);

        $with = $this->mergeRelations($source, $sheet, $fields);
        $query = $source->baseQuery($sheet->filters);

        if ($with !== []) {
            $query->with($with);
        }

        $rowsFactory = function () use ($query, $sheet, $fields): \Generator {
            yield from $this->streamRows($query, $sheet, $fields);
        };

        return new ResolvedSheet(sheet: $sheet, fields: $fields, rowsFactory: $rowsFactory);
    }

    /**
     * @return array<int, ExportFieldDto>
     */
    private function prepareFields(ExportSourceInterface $source, ExportSheetDto $sheet): array
    {
        if ($sheet->fields === []) {
            // Si aucun champ n'est demandé, on prend tous les champs publiés par la source.
            return array_map(
                static fn ($schema) => ExportFieldDto::fromSchema($schema),
                array_values($source->fieldsSchema())
            );
        }

        $schemas = $source->fieldsSchema();
        $prepared = [];

        foreach ($sheet->fields as $field) {
            if ($field->key === '') {
                continue;
            }

            $schema = $schemas[$field->key] ?? null;

            // Champ libre (path explicite) : on l'autorise tel quel.
            if ($schema === null) {
                if ($field->path === null) {
                    throw InvalidFieldException::forKey($source->key(), $field->key);
                }

                $prepared[] = $field;

                continue;
            }

            // Champ catalogué : on fusionne schéma + override client.
            $prepared[] = ExportFieldDto::fromSchema($schema, [
                'label' => $field->label !== '' && $field->label !== $field->key ? $field->label : $schema->label,
                'path' => $field->path ?? $schema->path,
                'type' => $field->type->value,
                'format' => $field->format,
                'default' => $field->default ?? $schema->default,
                'locale' => $field->locale,
            ]);
        }

        if ($prepared === []) {
            throw new ExportException(
                "La feuille « {$sheet->name} » ne contient aucun champ exportable valide."
            );
        }

        return $prepared;
    }

    /**
     * Fusionne les relations à charger : defaultWith() + requiresWith des champs choisis + with utilisateur.
     *
     * @param  array<int, ExportFieldDto>  $fields
     * @return array<int, string>
     */
    private function mergeRelations(ExportSourceInterface $source, ExportSheetDto $sheet, array $fields): array
    {
        $with = array_merge($source->defaultWith(), $sheet->with);

        $schemas = $source->fieldsSchema();
        foreach ($fields as $field) {
            $schema = $schemas[$field->key] ?? null;
            if ($schema !== null && $schema->requiresWith !== []) {
                $with = array_merge($with, $schema->requiresWith);
            }
        }

        $with = array_values(array_unique(array_filter(array_map('strval', $with))));

        return $with;
    }

    /**
     * Stream paresseux : on parcourt les enregistrements en chunk pour
     * éviter de charger toute la table en mémoire.
     *
     * @param  array<int, ExportFieldDto>  $fields
     */
    private function streamRows(Builder $query, ExportSheetDto $sheet, array $fields): \Generator
    {
        $chunkSize = $sheet->chunkSize > 0 ? $sheet->chunkSize : 500;

        // lazyById() conserve l'ordre par clé primaire et évite les soucis d'offset.
        foreach ($query->lazyById($chunkSize) as $model) {
            yield $this->mapRow($model, $fields);
        }
    }

    /**
     * @param  array<int, ExportFieldDto>  $fields
     * @return array<string,mixed>
     */
    private function mapRow(mixed $model, array $fields): array
    {
        $row = [];
        foreach ($fields as $field) {
            $raw = $this->paths->resolve($model, $field->resolvePath());
            $row[$field->key] = $this->formatter->format($raw, $field);
        }

        return $row;
    }
}
