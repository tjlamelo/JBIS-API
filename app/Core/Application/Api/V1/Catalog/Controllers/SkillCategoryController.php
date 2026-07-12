<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Catalog\Models\SkillCategory;
use App\Core\Domain\Shared\Support\TranslatableCatalogSearch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $items = SkillCategory::query()
            ->when($search, function ($query, $search): void {
                TranslatableCatalogSearch::apply($query, 'name', (string) $search, ['slug']);
            })
            ->select(['id', 'name', 'slug'])
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($items);
    }
}
