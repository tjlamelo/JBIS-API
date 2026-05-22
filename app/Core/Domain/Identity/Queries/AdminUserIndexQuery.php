<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Queries;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class AdminUserIndexQuery
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $request->integer('per_page', 20)));
        $search = trim((string) $request->query('search', ''));
        $active = $request->query('active');
        $verified = $request->query('verified');
        $role = trim((string) $request->query('role', ''));
        $sectorIds = $request->query('sector_ids');
        if (is_string($sectorIds)) {
            $sectorIds = array_filter(array_map('intval', explode(',', $sectorIds)));
        } elseif (is_array($sectorIds)) {
            $sectorIds = array_values(array_filter(array_map('intval', $sectorIds)));
        } else {
            $sectorIds = [];
        }

        return User::query()
            ->with(['roles:id,name', 'sectors:id,name,slug'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number1', 'like', "%{$search}%");
                });
            })
            ->when($active === '1' || $active === 'true', fn ($query) => $query->where('active', true))
            ->when($active === '0' || $active === 'false', fn ($query) => $query->where('active', false))
            ->when($verified === '1' || $verified === 'true', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when($verified === '0' || $verified === 'false', fn ($query) => $query->whereNull('email_verified_at'))
            ->when($role !== '', function ($query) use ($role): void {
                $query->whereHas('roles', function ($roleQuery) use ($role): void {
                    $roleQuery->where('name', $role);
                });
            })
            ->when($sectorIds !== [], function ($query) use ($sectorIds): void {
                $query->whereHas('sectors', function ($sectorQuery) use ($sectorIds): void {
                    $sectorQuery->whereIn('offer_categories.id', $sectorIds);
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
