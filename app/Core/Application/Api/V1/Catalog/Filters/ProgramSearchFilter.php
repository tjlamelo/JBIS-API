<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class ProgramSearchFilter implements Filter
{
    /**
     * @param Builder $query C'est en réalité ton ProgramBuilder
     * @param mixed $value La valeur tapée (ex: "Canada")
     * @param string $property Le nom du filtre dans l'URL (ex: "search")
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        // On utilise la méthode search() que tu as définie dans ton ProgramBuilder
        $query->search((string) $value);
    }
}