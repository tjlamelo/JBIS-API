<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Location\Models\LanguageLevel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageLevelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $items = LanguageLevel::query()
            ->where('is_active', true)
            ->when($search, function ($query, $search) {
                $query->where('name->fr', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->select(['id', 'code', 'name', 'sort_order'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return response()->json($items);
    }
}
