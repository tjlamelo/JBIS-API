<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Queries;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\AdminUserSearchFilterApplicator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class AdminUserIndexQuery
{
    public function __construct(
        private readonly AdminUserSearchFilterApplicator $filters,
    ) {}

    public function paginate(Request $request): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $request->integer('per_page', 20)));
        $sortBy = (string) $request->query('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['created_at', 'name', 'email', 'updated_at', 'date_of_birth'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        $query = User::query()
            ->with(['roles:id,name', 'sectors:id,name,slug', 'profile']);

        $this->filters->apply($query, $request->query());

        if ($sortBy === 'date_of_birth') {
            $query->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                ->select('users.*')
                ->orderBy('user_profiles.'.$sortBy, $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query->paginate($perPage);
    }
}
