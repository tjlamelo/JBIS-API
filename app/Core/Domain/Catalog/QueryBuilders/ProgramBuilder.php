<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\QueryBuilders;

use App\Core\Domain\Catalog\States\ProgramStatus;
use Illuminate\Database\Eloquent\Builder;

class ProgramBuilder extends Builder
{
    public function search(string $term): self
    {
        return $this->where(function (Builder $query) use ($term): void {
            $locale = app()->getLocale();

            if (in_array($locale, ['fr', 'en'])) {
                $searchTerm = $term.'*';

                $query->whereRaw(
                    "MATCH(name_{$locale}, description_{$locale}) AGAINST(? IN BOOLEAN MODE)",
                    [$searchTerm]
                );
            } else {
                $query->where('name->'.$locale, 'like', "%{$term}%")
                    ->orWhere('description->'.$locale, 'like', "%{$term}%");
            }
        });
    }

    /**
     * Programmes publiés (visibles côté catalogue public).
     */
    public function active(): self
    {
        return $this->where('status', ProgramStatus::Published->value);
    }

    public function validDates(): self
    {
        return $this->where(function (Builder $query) {
            $now = now()->toDateString();

            $query->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            });
        });
    }

    public function byZoneId($zoneId): self
    {
        return $this->where('geographic_zone_id', $zoneId);
    }

    public function withActiveOffers(): self
    {
        return $this->whereHas('offers', function (Builder $query) {
            $query->published();
        });
    }
}
