<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Controllers\ProcessFlow;

use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessFlowSection;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Catalogue des clés de section distinctes (process_flow_sections.key).
 */
final class ProcessFlowSectionKeyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ProcessFlow::class);

        $search = trim((string) $request->query('search', ''));
        $perPage = max(1, min(100, $request->integer('per_page', 20)));

        $paginator = ProcessFlowSection::query()
            ->selectRaw('`key`, MAX(id) as id')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('key', 'like', "%{$search}%")
                        ->orWhere('title->fr', 'like', "%{$search}%")
                        ->orWhere('title->en', 'like', "%{$search}%");
                });
            })
            ->groupBy('key')
            ->orderBy('key')
            ->paginate($perPage);

        $representatives = ProcessFlowSection::query()
            ->whereIn('id', $paginator->getCollection()->pluck('id'))
            ->get()
            ->keyBy('id');

        $data = $paginator->getCollection()->map(function ($row) use ($representatives) {
            $section = $representatives->get($row->id);

            return [
                'key' => $row->key,
                'title' => $section?->getTranslations('title') ?? [],
                'icon' => $section?->icon,
                'color' => $section?->color,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }
}
