<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Location\Models\Language;
use App\Core\Domain\Shared\Support\TranslatableCatalogSearch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $items = Language::query()
            ->when($search !== '', function ($query) use ($search): void {
                TranslatableCatalogSearch::apply($query, 'name', $search, ['code']);
            })
            ->select(['id', 'name', 'code'])
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($items);
    }
}
