<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Location\Models\Country;
use App\Core\Infrastructure\Cache\AppCache;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct(
        private readonly AppCache $cache,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $countryId = $request->query('country_id');

        if ($countryId !== null && $countryId !== '') {
            return response()->json($this->fetchCountryById((int) $countryId));
        }

        if ($search) {
            $perPage = (int) $request->query('per_page', 100);
            $page = max(1, (int) $request->query('page', 1));

            return response()->json($this->fetchCountries($request, $page, $perPage));
        }

        $perPage = (int) $request->query('per_page', 100);
        $page = max(1, (int) $request->query('page', 1));
        $payload = $this->cache->remember(
            $this->cache->referenceKey('countries', app()->getLocale(), "p{$perPage}:page{$page}"),
            86400,
            fn () => $this->fetchCountries($request, $page, $perPage),
        );

        return response()->json($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCountryById(int $countryId): array
    {
        $item = Country::query()
            ->where('is_active', true)
            ->where('id', $countryId)
            ->select(['id', 'name', 'code'])
            ->first();

        return [
            'data' => $item ? [$item->toArray()] : [],
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 1,
            'total' => $item ? 1 : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCountries(Request $request, int $page, int $perPage): array
    {
        $search = $request->query('search');

        return Country::query()
            ->where('is_active', true)
            ->when($search, function ($query, $search) {
                $query->where('name->fr', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->select(['id', 'name', 'code'])
            ->orderBy('name->fr')
            ->paginate($perPage, ['*'], 'page', $page)
            ->toArray();
    }
}
