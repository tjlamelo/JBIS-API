<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Location\Models\City;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $countryId = $request->query('country_id');

        $items = City::query()
            ->with('region:id,name,country_id')
            ->when($countryId, function ($query, $countryId) {
                $query->whereHas('region', function ($regionQuery) use ($countryId) {
                    $regionQuery->where('country_id', $countryId);
                });
            })
            ->when($search, function ($query, $search) {
                $query->where('name->fr', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->select(['id', 'name', 'slug', 'region_id'])
            ->orderBy('name->fr')
            ->paginate((int) $request->query('per_page', 100));

        return response()->json($items);
    }
}
