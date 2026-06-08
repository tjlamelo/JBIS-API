<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Queries\Program;

use App\Core\Domain\Catalog\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProgramIndexQuery extends QueryBuilder
{
    public function __construct(?Request $request = null)
    {
        parent::__construct(Program::query(), $request ?? request());

        $this->allowedFilters([
            AllowedFilter::callback('search', fn ($query, $value) => $query->search((string) $value)),

            AllowedFilter::callback('geographic_zone_id', function (Builder $query, $value): void {
                $values = is_array($value) ? $value : explode(',', (string) $value);
                $ids = array_values(array_filter(array_map('intval', $values)));
                if ($ids === []) {
                    return;
                }
                $query->whereIn('geographic_zone_id', $ids);
            }),
            AllowedFilter::exact('status'),

            AllowedFilter::callback('is_featured', function ($query, $value): void {
                $query->where('is_featured', filter_var($value, FILTER_VALIDATE_BOOLEAN));
            }),
            AllowedFilter::callback('is_urgent', function ($query, $value): void {
                $query->where('is_urgent', filter_var($value, FILTER_VALIDATE_BOOLEAN));
            }),

            AllowedFilter::callback('min_age', fn ($query, $value) => $query->where(function ($q) use ($value) {
                $v = (int) $value;
                $q->whereNull('age_max')
                    ->orWhere('age_max', '>=', $v);
            })),
            AllowedFilter::callback('max_age', fn ($query, $value) => $query->where(function ($q) use ($value) {
                $v = (int) $value;
                $q->whereNull('age_min')
                    ->orWhere('age_min', '<=', $v);
            })),
        ])
            ->allowedSorts(['name_fr', 'name_en', 'views_count', 'created_at'])
            ->defaultSort('-created_at');
    }

    public function public(): self
    {
        return $this->active()->validDates();
    }
}
