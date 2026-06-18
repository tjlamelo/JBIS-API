<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Catalog\Services\CatalogAdminService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class AdminCatalogController extends Controller
{
    public function __construct(
        private readonly CatalogAdminService $catalogAdmin,
    ) {}

    public function resources(): JsonResponse
    {
        return BaseResponse::ok([
            'resources' => $this->catalogAdmin->listResources(),
        ])->toJsonResponse();
    }

    public function index(string $resource, Request $request): JsonResponse
    {
        $config = $this->catalogAdmin->resolve($resource);
        $search = trim((string) $request->query('search', ''));

        $query = $this->catalogAdmin->newQuery($resource);

        if ($search !== '') {
            $query->where(function ($inner) use ($search, $config): void {
                if (in_array('slug', $config['fillable'] ?? [], true)) {
                    $inner->orWhere('slug', 'like', "%{$search}%");
                }
                if (in_array('code', $config['fillable'] ?? [], true)) {
                    $inner->orWhere('code', 'like', "%{$search}%");
                }
                foreach ($config['translatable'] ?? [] as $field) {
                    $inner->orWhere("{$field}->fr", 'like', "%{$search}%")
                        ->orWhere("{$field}->en", 'like', "%{$search}%");
                }
            });
        }

        $items = $query->orderByDesc('id')->paginate($request->integer('per_page', 25));

        return BaseResponse::ok([
            'items' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
            'resource' => [
                'key' => $resource,
                'label' => $config['label'] ?? $resource,
            ],
        ])->toJsonResponse();
    }

    public function show(string $resource, int $id): JsonResponse
    {
        $item = $this->catalogAdmin->find($resource, $id);

        return BaseResponse::ok(['item' => $item])->toJsonResponse();
    }

    public function store(string $resource, Request $request): JsonResponse
    {
        $config = $this->catalogAdmin->resolve($resource);
        $validated = $this->validatePayload($config, $request);
        $item = $this->catalogAdmin->create($resource, $validated);

        return BaseResponse::created([
            'message' => __('Élément créé.'),
            'item' => $item,
        ])->toJsonResponse();
    }

    public function update(string $resource, int $id, Request $request): JsonResponse
    {
        $config = $this->catalogAdmin->resolve($resource);
        $item = $this->catalogAdmin->find($resource, $id);
        $validated = $this->validatePayload($config, $request, partial: true);
        $item = $this->catalogAdmin->update($resource, $item, $validated);

        return BaseResponse::ok([
            'message' => __('Élément mis à jour.'),
            'item' => $item,
        ])->toJsonResponse();
    }

    public function destroy(string $resource, int $id): JsonResponse
    {
        $item = $this->catalogAdmin->find($resource, $id);
        $this->catalogAdmin->delete($resource, $item);

        return BaseResponse::ok([
            'message' => __('Élément supprimé.'),
        ])->toJsonResponse();
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function validatePayload(array $config, Request $request, bool $partial = false): array
    {
        $rules = $config['rules'] ?? [];

        if ($partial) {
            foreach (array_keys($rules) as $key) {
                if (is_array($rules[$key])) {
                    $rules[$key] = array_merge(['sometimes'], $rules[$key]);
                }
            }
        }

        return Validator::make($request->all(), $rules)->validate();
    }
}
