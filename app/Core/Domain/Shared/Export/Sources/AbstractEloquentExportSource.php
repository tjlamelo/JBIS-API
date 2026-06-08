<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Shared\Export\Contracts\ExportSourceInterface;
use App\Core\Domain\Shared\Export\DTOs\ExportFieldSchemaDto;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Squelette d'une source Eloquent.
 *
 * Une source concrète n'a qu'à :
 *  - déclarer sa clé (`key()`), son libellé (`label()`) et son modèle (`modelClass()`),
 *  - publier ses champs exportables via `fields()`,
 *  - éventuellement personnaliser `applyFilters()` pour gérer des filtres custom.
 */
abstract class AbstractEloquentExportSource implements ExportSourceInterface
{
    public function modelClass(): string
    {
        throw new \LogicException(static::class.'::modelClass() doit être implémenté.');
    }

    public function defaultWith(): array
    {
        return [];
    }

    public function baseQuery(array $filters = []): Builder
    {
        $class = $this->modelClass();
        /** @var Model $model */
        $model = new $class;

        $query = $model::query();

        if ($with = $this->defaultWith()) {
            $query->with($with);
        }

        return $this->applyFilters($query, $filters);
    }

    /**
     * Applique des filtres « génériques » communs à toutes les sources.
     *
     * Override possible dans les sources concrètes pour ajouter des filtres
     * spécifiques (par statut, par programme, etc.).
     *
     * Filtres pris en charge nativement :
     *  - ids: list<int>                → whereIn('id', …)
     *  - created_after: string         → where('created_at', '>=', …)
     *  - created_before: string        → where('created_at', '<=', …)
     *  - search: string                → callback `applySearch` si défini
     *  - order_by: string              → orderBy(…)
     *  - order_dir: 'asc'|'desc'
     *  - limit: int
     *
     * @param  array<string,mixed>  $filters
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['ids']) && is_array($filters['ids'])) {
            $query->whereIn($query->getModel()->getKeyName(), array_values($filters['ids']));
        }

        if (! empty($filters['created_after'])) {
            $query->where('created_at', '>=', (string) $filters['created_after']);
        }

        if (! empty($filters['created_before'])) {
            $query->where('created_at', '<=', (string) $filters['created_before']);
        }

        if (! empty($filters['search']) && method_exists($this, 'applySearch')) {
            $this->applySearch($query, (string) $filters['search']);
        }

        // Sécurité : on ne laisse pas n'importe quelle colonne en order_by.
        if (! empty($filters['order_by']) && is_string($filters['order_by'])) {
            $dir = strtolower((string) ($filters['order_dir'] ?? 'asc'));
            $dir = in_array($dir, ['asc', 'desc'], true) ? $dir : 'asc';
            $query->orderBy($filters['order_by'], $dir);
        }

        if (! empty($filters['limit']) && is_numeric($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }

        if (! empty($filters['user_id'])) {
            $model = $query->getModel();
            if (in_array('user_id', $model->getFillable(), true)) {
                $query->where($model->qualifyColumn('user_id'), (int) $filters['user_id']);
            }
        }

        return $this->applyCustomFilters($query, $filters);
    }

    /**
     * Point d'extension pour les sources concrètes (override safe).
     *
     * @param  array<string,mixed>  $filters
     */
    protected function applyCustomFilters(Builder $query, array $filters): Builder
    {
        return $query;
    }

    public function fieldsSchema(): array
    {
        $schema = [];
        foreach ($this->fields() as $field) {
            $schema[$field->key] = $field;
        }

        return $schema;
    }

    /**
     * Liste des champs exportables.
     *
     * @return array<int, ExportFieldSchemaDto>
     */
    abstract protected function fields(): array;

    /**
     * Helper pour créer rapidement un schéma de champ.
     *
     * @param  array<int, string>  $requiresWith
     */
    protected function field(
        string $key,
        string $label,
        ?string $path = null,
        ExportFieldType $type = ExportFieldType::String,
        ?string $format = null,
        mixed $default = null,
        ?string $group = null,
        ?string $description = null,
        array $requiresWith = [],
    ): ExportFieldSchemaDto {
        return new ExportFieldSchemaDto(
            key: $key,
            label: $label,
            path: $path ?? $key,
            type: $type,
            format: $format,
            default: $default,
            group: $group,
            description: $description,
            requiresWith: $requiresWith,
        );
    }
}
