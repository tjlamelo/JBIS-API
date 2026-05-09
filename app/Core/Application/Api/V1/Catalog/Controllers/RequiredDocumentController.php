<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequiredDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $items = RequiredDocument::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            })
            ->select(['id', 'name', 'slug', 'type', 'description'])
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($items);
    }
}
