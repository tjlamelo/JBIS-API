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

        $items = City::query()
            ->with('region:id,name,country_id')
            ->when($search, function ($query, $search) {
                $query->where('name->fr', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->select(['id', 'name', 'slug', 'region_id'])
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($items);
    }
}
