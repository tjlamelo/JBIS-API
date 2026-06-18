<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Location\Models\Region;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $countryId = $request->integer('country_id');

        $items = Region::query()
            ->with('country:id,code,name')
            ->when($countryId > 0, fn ($query) => $query->where('country_id', $countryId))
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name->fr', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->select(['id', 'name', 'slug', 'country_id'])
            ->orderBy('name->fr')
            ->orderBy('id')
            ->paginate((int) $request->integer('per_page', 50));

        return response()->json($items);
    }
}
