<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Controllers\ProcessFlow;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Workflow\Queries\ProcessFlow\ProcessFlowIndexQuery;
use App\Core\Application\Api\V1\Workflow\Requests\ProcessFlow\StoreProcessFlowRequest;
use App\Core\Application\Api\V1\Workflow\Requests\ProcessFlow\UpdateProcessFlowRequest;
use App\Core\Application\Api\V1\Workflow\Resources\ProcessFlow\ProcessFlowResource;
use App\Core\Domain\Workflow\Actions\ProcessFlow\CreateProcessFlowAction;
use App\Core\Domain\Workflow\Actions\ProcessFlow\DeleteProcessFlowAction;
use App\Core\Domain\Workflow\Actions\ProcessFlow\PublishProcessFlowVersionAction;
use App\Core\Domain\Workflow\Actions\ProcessFlow\UpdateProcessFlowAction;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminProcessFlowController extends Controller
{
    public function __construct(
        private readonly CreateProcessFlowAction $createProcessFlowAction,
        private readonly UpdateProcessFlowAction $updateProcessFlowAction,
        private readonly DeleteProcessFlowAction $deleteProcessFlowAction,
        private readonly PublishProcessFlowVersionAction $publishProcessFlowVersionAction,
    ) {}

    public function index(Request $request, ProcessFlowIndexQuery $query): JsonResponse
    {
        $this->authorize('viewAny', ProcessFlow::class);

        $flows = $query
            ->with(['program', 'offer', 'country'])
            ->withCount('steps')
            ->paginate($request->integer('per_page', 15));

        return BaseResponse::ok([
            'process_flows' => ProcessFlowResource::collection($flows),
            'meta' => [
                'current_page' => $flows->currentPage(),
                'last_page' => $flows->lastPage(),
                'total' => $flows->total(),
                'per_page' => $flows->perPage(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreProcessFlowRequest $request): JsonResponse
    {
        $flow = $this->createProcessFlowAction->execute($request->toDto());
        $flow->load(['program', 'offer', 'country', 'sections.steps', 'steps']);

        return BaseResponse::created([
            'message' => __('Parcours cree avec succes.'),
            'process_flow' => new ProcessFlowResource($flow),
        ])->toJsonResponse();
    }

    public function show(ProcessFlow $processFlow): JsonResponse
    {
        $this->authorize('view', $processFlow);

        $processFlow->load(['program', 'offer', 'country', 'sections.steps', 'steps']);

        return BaseResponse::ok([
            'process_flow' => new ProcessFlowResource($processFlow),
        ])->toJsonResponse();
    }

    public function update(UpdateProcessFlowRequest $request, ProcessFlow $processFlow): JsonResponse
    {
        $flow = $this->updateProcessFlowAction->execute($processFlow->id, $request->toDto());
        $flow->load(['program', 'offer', 'country', 'sections.steps', 'steps']);

        return BaseResponse::ok([
            'message' => __('Parcours mis a jour avec succes.'),
            'process_flow' => new ProcessFlowResource($flow),
        ])->toJsonResponse();
    }

    public function destroy(ProcessFlow $processFlow): JsonResponse
    {
        $this->authorize('delete', $processFlow);

        $this->deleteProcessFlowAction->execute($processFlow->id);

        return BaseResponse::ok([
            'message' => __('Parcours supprime avec succes.'),
        ])->toJsonResponse();
    }

    public function publish(ProcessFlow $processFlow): JsonResponse
    {
        $this->authorize('publish', $processFlow);

        $flow = $this->publishProcessFlowVersionAction->execute($processFlow->id);
        $flow->load(['program', 'offer', 'country', 'sections.steps', 'steps']);

        return BaseResponse::created([
            'message' => __('Nouvelle version du parcours publiee.'),
            'process_flow' => new ProcessFlowResource($flow),
        ])->toJsonResponse();
    }
}
