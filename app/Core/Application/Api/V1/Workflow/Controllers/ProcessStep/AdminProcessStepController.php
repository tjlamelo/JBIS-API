<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Controllers\ProcessStep;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Workflow\Requests\ProcessStep\StoreProcessStepRequest;
use App\Core\Application\Api\V1\Workflow\Requests\ProcessStep\UpdateProcessStepRequest;
use App\Core\Application\Api\V1\Workflow\Resources\ProcessStep\ProcessStepResource;
use App\Core\Domain\Workflow\Actions\ProcessStep\CreateProcessStepAction;
use App\Core\Domain\Workflow\Actions\ProcessStep\DeleteProcessStepAction;
use App\Core\Domain\Workflow\Actions\ProcessStep\UpdateProcessStepAction;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class AdminProcessStepController extends Controller
{
    public function __construct(
        private readonly CreateProcessStepAction $createProcessStepAction,
        private readonly UpdateProcessStepAction $updateProcessStepAction,
        private readonly DeleteProcessStepAction $deleteProcessStepAction,
    ) {}

    public function store(StoreProcessStepRequest $request): JsonResponse
    {
        $step = $this->createProcessStepAction->execute($request->toDto());

        return BaseResponse::created([
            'message' => __('Etape creee avec succes.'),
            'process_step' => new ProcessStepResource($step),
        ])->toJsonResponse();
    }

    public function update(UpdateProcessStepRequest $request, ProcessStep $processStep): JsonResponse
    {
        $step = $this->updateProcessStepAction->execute($processStep->id, $request->toDto());

        return BaseResponse::ok([
            'message' => __('Etape mise a jour avec succes.'),
            'process_step' => new ProcessStepResource($step),
        ])->toJsonResponse();
    }

    public function destroy(ProcessStep $processStep): JsonResponse
    {
        $this->authorize('delete', $processStep);

        $this->deleteProcessStepAction->execute($processStep->id);

        return BaseResponse::ok([
            'message' => __('Etape supprimee avec succes.'),
        ])->toJsonResponse();
    }
}
