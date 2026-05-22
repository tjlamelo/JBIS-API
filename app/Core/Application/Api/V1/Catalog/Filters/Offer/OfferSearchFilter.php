<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Filters\Offer;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class OfferSearchFilter implements Filter
{
    /**
     * @param  Builder  $query  C'est en réalité notre OfferBuilder
     * @param  mixed  $value  La valeur tapée par l'utilisateur (ex: "développeur")
     * @param  string  $property  Le nom du filtre dans l'URL (ex: "search")
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        // On délègue tout le travail complexe à notre Couche Domaine !
        $query->search((string) $value);
    }
}
