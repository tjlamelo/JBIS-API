<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Catalog\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $categories = Category::query()
            ->where('is_active', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name->fr', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")
                        ->orWhere('description->fr', 'like', "%{$search}%")
                        ->orWhere('description->en', 'like', "%{$search}%");
                });
            })
            ->select(['id', 'name', 'slug', 'description'])
            ->orderBy('name->fr')
            ->orderBy('id')
            ->paginate((int) $request->integer('per_page', 50));

        return response()->json($categories);
    }
}
