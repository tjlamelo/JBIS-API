<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Application\Api\V1\Catalog\Resources\Training\TrainingResource;
use App\Core\Domain\Catalog\Models\Training;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TrainingController extends Controller
{
    /**
     * Catalogue formations actives (candidat / staff — sélection inscription).
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $items = Training::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('domain', 'like', "%{$search}%")
                        ->orWhere('organization', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->paginate(min(100, max(1, (int) $request->query('per_page', 50))));

        return TrainingResource::collection($items)->response();
    }

    public function show(Training $training): JsonResponse
    {
        if (! $training->is_active) {
            abort(404);
        }

        return response()->json([
            'data' => new TrainingResource($training),
        ]);
    }
}
