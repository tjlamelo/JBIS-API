<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Contracts;

use App\Core\Domain\Shared\Export\DTOs\ExportFieldSchemaDto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Représente une source de données exportable (un agrégat racine Eloquent).
 *
 * Chaque source :
 *  - expose une clé stable utilisée dans les requêtes API (« users », « offers »…),
 *  - construit une requête Eloquent filtrable,
 *  - publie le catalogue des champs exportables (utilisé par l'API de schéma).
 */
interface ExportSourceInterface
{
    /**
     * Clé unique stable (ex. "users", "applications").
     */
    public function key(): string;

    /**
     * Libellé humain affichable côté admin/UI.
     */
    public function label(): string;

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string;

    /**
     * Construit la requête de base à partir des filtres fournis.
     *
     * @param  array<string,mixed>  $filters
     */
    public function baseQuery(array $filters = []): Builder;

    /**
     * Relations Eloquent chargées par défaut (eager loading) pour cette source.
     *
     * @return array<int, string>
     */
    public function defaultWith(): array;

    /**
     * Catalogue des champs exportables (clé → schéma).
     *
     * @return array<string, ExportFieldSchemaDto>
     */
    public function fieldsSchema(): array;
}
