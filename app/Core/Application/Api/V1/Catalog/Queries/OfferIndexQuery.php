<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Queries;

use App\Core\Application\Api\V1\Catalog\Filters\OfferSearchFilter;
use App\Core\Domain\Catalog\Models\Offer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OfferIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        // 1. Optimisation N+1 : On charge toutes les relations nécessaires au rendu
        $query = Offer::query()->with(['company', 'program', 'category', 'country']);

        parent::__construct($query, $request);

        $this->allowedFilters([
            // Filtre de recherche FullText (MariaDB)
            AllowedFilter::custom('search', new OfferSearchFilter()),
            
            AllowedFilter::exact('status'),
            
            // 🔥 Catégorie : On accepte 'offer_category_id' (depuis le front) OU 'category'
            AllowedFilter::callback('offer_category_id', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string)$value);
                $query->whereIn('offer_category_id', $values);
            }),
            AllowedFilter::callback('category', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string)$value);
                $query->whereIn('offer_category_id', $values);
            }),

            // 🔥 Pays : On utilise country_id pour correspondre à ta nouvelle migration
            AllowedFilter::callback('country_id', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string)$value);
                $query->whereIn('country_id', $values);
            }),
            AllowedFilter::callback('country', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string)$value);
                $query->whereIn('country_id', $values);
            }),

            // Type de contrat (JSON multilingue)
            AllowedFilter::callback('contract_type', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string)$value);
                $locale = app()->getLocale();
                $query->whereIn("contract_type->{$locale}", $values);
            }),

            AllowedFilter::exact('location'),
            AllowedFilter::exact('program_id'),
            AllowedFilter::callback('is_urgent', function (Builder $query, $value) {
                $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($boolValue === null) {
                    return;
                }

                $query->where('meta->is_urgent', $boolValue);
            }),
        ])
        ->allowedSorts(['published_at', 'salary_min', 'created_at', 'title'])
        ->defaultSort('-published_at');
    }
}