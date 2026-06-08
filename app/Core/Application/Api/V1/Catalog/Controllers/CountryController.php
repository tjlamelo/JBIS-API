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

        if ($search) {
            return response()->json($this->fetchCountries($request));
        }

        $perPage = (int) $request->query('per_page', 100);
        $payload = $this->cache->remember(
            $this->cache->referenceKey('countries', app()->getLocale(), "p{$perPage}"),
            86400,
            fn () => $this->fetchCountries($request),
        );

        return response()->json($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCountries(Request $request): array
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
            ->paginate((int) $request->query('per_page', 100))
            ->toArray();
    }
}
