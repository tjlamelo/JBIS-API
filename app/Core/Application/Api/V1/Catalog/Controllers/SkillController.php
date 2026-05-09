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

        $items = Skill::query()
            ->when($search, function ($query, $search) {
                $query->where('name->fr', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($skillCategoryId, fn ($query) => $query->where('skill_category_id', $skillCategoryId))
            ->with('category:id,name,slug')
            ->select(['id', 'name', 'slug', 'skill_category_id'])
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($items);
    }
}
