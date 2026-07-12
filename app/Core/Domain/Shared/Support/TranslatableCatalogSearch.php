<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Normalizer;

final class TranslatableCatalogSearch
{
    public static function fold(string $value): string
    {
        $normalized = Normalizer::normalize($value, Normalizer::FORM_D);
        if (! is_string($normalized)) {
            $normalized = $value;
        }

        $stripped = preg_replace('/\p{Mn}/u', '', $normalized);

        return mb_strtolower(is_string($stripped) ? $stripped : $normalized);
    }

    /**
     * @param  list<string>  $plainColumns
     */
    public static function apply(Builder $query, string $jsonColumn, string $search, array $plainColumns = []): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $terms = array_values(array_unique(array_filter([
            $search,
            self::fold($search),
        ], static fn (string $term): bool => $term !== '')));

        $useCollation = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
        $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $jsonColumn) ?: 'name';

        $query->where(function (Builder $outer) use ($safeColumn, $terms, $plainColumns, $useCollation): void {
            foreach ($terms as $term) {
                $like = '%'.$term.'%';
                $outer->orWhere(function (Builder $inner) use ($safeColumn, $like, $plainColumns, $useCollation): void {
                    if ($useCollation) {
                        $inner->whereRaw(
                            "JSON_UNQUOTE(JSON_EXTRACT(`{$safeColumn}`, '$.fr')) COLLATE utf8mb4_general_ci LIKE ?",
                            [$like]
                        )->orWhereRaw(
                            "JSON_UNQUOTE(JSON_EXTRACT(`{$safeColumn}`, '$.en')) COLLATE utf8mb4_general_ci LIKE ?",
                            [$like]
                        );
                    } else {
                        $inner->where("{$safeColumn}->fr", 'like', $like)
                            ->orWhere("{$safeColumn}->en", 'like', $like);
                    }

                    foreach ($plainColumns as $column) {
                        $inner->orWhere($column, 'like', $like);
                    }
                });
            }
        });
    }
}
