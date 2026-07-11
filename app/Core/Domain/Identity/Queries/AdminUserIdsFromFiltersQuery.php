<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Queries;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\AdminUserSearchFilterApplicator;
use Illuminate\Support\Collection;

final class AdminUserIdsFromFiltersQuery
{
    public const MAX_BULK_IDS = 500;

    public function __construct(
        private readonly AdminUserSearchFilterApplicator $filters,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return Collection<int, int>
     */
    public function collect(array $params, bool $onlyApproved = true, int $limit = self::MAX_BULK_IDS): Collection
    {
        $limit = max(1, min(self::MAX_BULK_IDS, $limit));

        $query = User::query()->select('users.id');

        $this->filters->apply($query, $params);

        if ($onlyApproved) {
            $query->whereHas('profile', fn ($q) => $q->where('is_approved', true));
        }

        return $query->orderByDesc('users.id')->limit($limit)->pluck('users.id');
    }
}
