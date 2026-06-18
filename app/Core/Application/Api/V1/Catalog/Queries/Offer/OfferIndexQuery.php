<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Queries\Offer;

use App\Core\Application\Api\V1\Catalog\Filters\Offer\OfferSearchFilter;
use App\Core\Domain\Catalog\Models\ContractType;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Location\Models\Country;
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
            AllowedFilter::custom('search', new OfferSearchFilter),

            AllowedFilter::exact('status'),

            // 🔥 Catégorie : On accepte 'category_id' (depuis le front) OU 'category'
            AllowedFilter::callback('category_id', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string) $value);
                $query->whereIn('category_id', $values);
            }),
            AllowedFilter::callback('category', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string) $value);
                $query->whereIn('category_id', $values);
            }),

            // 🔥 Pays : On utilise country_id pour correspondre à ta nouvelle migration
            AllowedFilter::callback('country_id', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string) $value);
                $query->whereIn('country_id', $values);
            }),
            AllowedFilter::callback('country', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string) $value);
                $query->whereIn('country_id', $values);
            }),

            // Types de contrat : libellés (ex. CDI, Stage) comparés au champ JSON `name` de contract_types
            AllowedFilter::callback('contract_type', function (Builder $query, $value) {
                $values = is_array($value) ? $value : explode(',', (string) $value);
                $labels = array_values(array_filter(array_map('trim', $values), static fn ($v) => $v !== ''));
                if ($labels === []) {
                    return;
                }

                $locale = in_array(app()->getLocale(), ['fr', 'en'], true) ? app()->getLocale() : 'fr';

                $ids = ContractType::query()
                    ->where(function (Builder $ct) use ($labels, $locale): void {
                        foreach ($labels as $label) {
                            $ct->orWhere("name->{$locale}", $label);
                        }
                    })
                    ->pluck('id')
                    ->all();

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('contract_type_id', $ids);
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

            AllowedFilter::exact('work_mode'),

            AllowedFilter::callback('salary_min', function (Builder $query, $value) {
                $min = is_numeric($value) ? (float) $value : null;
                if ($min === null || $min <= 0) {
                    return;
                }

                $query->where(function (Builder $q) use ($min): void {
                    $q->where(function (Builder $public) use ($min): void {
                        $public->where('is_salary_public', true)
                            ->where(function (Builder $salary) use ($min): void {
                                $salary->where('salary_max', '>=', $min)
                                    ->orWhere('salary_min', '>=', $min);
                            });
                    });
                });
            }),

            AllowedFilter::callback('international', function (Builder $query, $value) {
                $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($boolValue !== true) {
                    return;
                }

                $cameroonId = Country::query()->where('code', 'CM')->value('id');
                if ($cameroonId === null) {
                    return;
                }

                $query->whereNotNull('country_id')
                    ->where('country_id', '!=', $cameroonId);
            }),
        ])
            ->allowedSorts(['published_at', 'salary_min', 'created_at', 'title'])
            ->defaultSort('-published_at');
    }
}
