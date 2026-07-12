<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Archive;

use App\Core\Domain\Identity\Models\Archive;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListArchivesAction
{
    /**
     * @param  array{category?: string|null, q?: string|null, related_user_id?: int|null, per_page?: int}  $filters
     * @return LengthAwarePaginator<int, Archive>
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $query = Archive::query()
            ->with(['uploader:id,name,email', 'relatedUser:id,name,email'])
            ->latest();

        if (! empty($filters['related_user_id'])) {
            $query->where('related_user_id', (int) $filters['related_user_id']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', strtoupper((string) $filters['category']));
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('original_name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 20)));

        return $query->paginate($perPage);
    }
}
