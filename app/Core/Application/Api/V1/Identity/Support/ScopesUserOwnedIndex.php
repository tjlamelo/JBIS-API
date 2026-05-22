<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Support;

use App\Core\Domain\Identity\Support\ApplicationPermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ScopesUserOwnedIndex
{
    protected function scopeIndexToUser(Request $request, Builder $query, string $resource): void
    {
        $user = $request->user();

        if ($request->filled('user_id') && $user?->can(ApplicationPermission::name($resource, ApplicationPermission::VIEW))) {
            $query->where('user_id', (int) $request->integer('user_id'));

            return;
        }

        $query->where('user_id', $user?->id);
    }
}
