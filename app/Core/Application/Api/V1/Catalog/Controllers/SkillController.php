<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Catalog\Models\Skill;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $skillCategoryId = $request->integer('skill_category_id');
        $categoryId = $request->integer('category_id');
        $categoryIds = [];
        $rawCategoryIds = $request->input('category_ids');
        if (is_array($rawCategoryIds)) {
            $categoryIds = array_values(array_unique(array_filter(array_map('intval', $rawCategoryIds))));
        } elseif (is_string($rawCategoryIds) && $rawCategoryIds !== '') {
            $categoryIds = array_values(array_unique(array_filter(array_map('intval', explode(',', $rawCategoryIds)))));
        }

        $items = Skill::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name->fr', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($skillCategoryId, fn ($query) => $query->where('skill_category_id', $skillCategoryId))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($categoryIds !== [] && ! $categoryId, fn ($query) => $query->whereIn('category_id', $categoryIds))
            ->with([
                'skillCategory:id,name,slug',
                'category:id,name,slug,description',
            ])
            ->select(['id', 'name', 'slug', 'skill_category_id', 'category_id'])
            ->orderBy('name->fr')
            ->orderBy('id')
            ->paginate(20);

        return response()->json($items);
    }
}
