<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Location\Models\LanguageLevel;
use App\Core\Domain\Shared\Support\TranslatableCatalogSearch;
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
            ->when($search, function ($query, $search): void {
                TranslatableCatalogSearch::apply($query, 'name', (string) $search, ['code']);
            })
            ->select(['id', 'code', 'name', 'sort_order'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return response()->json($items);
    }
}
