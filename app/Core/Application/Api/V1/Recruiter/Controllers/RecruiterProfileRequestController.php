<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\StoreRecruiterProfileRequestRequest;
use App\Core\Application\Api\V1\Recruiter\Requests\UpdateRecruiterProfileRequestRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterProfileRequestResource;
use App\Core\Domain\Recruiter\Actions\CreateRecruiterProfileRequestAction;
use App\Core\Domain\Recruiter\Actions\SubmitRecruiterProfileRequestAction;
use App\Core\Domain\Recruiter\Actions\UpdateRecruiterProfileRequestAction;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecruiterProfileRequestController extends Controller
{
    public function __construct(
        private readonly RecruiterAccess $access,
        private readonly CreateRecruiterProfileRequestAction $createRequest,
        private readonly UpdateRecruiterProfileRequestAction $updateRequest,
        private readonly SubmitRecruiterProfileRequestAction $submitRequest,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterProfileRequest::class);
        $organization = $this->access->primaryOrganization($request->user());
        if ($organization === null) {
            return BaseResponse::ok(['requests' => [], 'meta' => ['total' => 0]])->toJsonResponse();
        }

        $requests = RecruiterProfileRequest::query()
            ->where('recruiter_organization_id', $organization->id)
            ->with(['submittedBy:id,name,email'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'requests' => RecruiterProfileRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreRecruiterProfileRequestRequest $request): JsonResponse
    {
        $this->authorize('create', RecruiterProfileRequest::class);
        $organization = $this->access->primaryOrganization($request->user());
        if ($organization === null) {
            abort(403, __('Organisation recruteur introuvable.'));
        }

        $profileRequest = $this->createRequest->execute(
            $organization,
            $request->user(),
            $request->validated('title'),
            $request->criteriaPayload(),
            (int) ($request->validated('quantity_needed') ?? 10),
            $request->validated('note'),
        );

        return BaseResponse::created([
            'request' => new RecruiterProfileRequestResource($profileRequest),
        ])->toJsonResponse();
    }

    public function show(RecruiterProfileRequest $profileRequest): JsonResponse
    {
        $this->authorize('view', $profileRequest);
        $profileRequest->load(['organization', 'submittedBy:id,name,email']);

        return BaseResponse::ok([
            'request' => new RecruiterProfileRequestResource($profileRequest),
        ])->toJsonResponse();
    }

    public function update(UpdateRecruiterProfileRequestRequest $request, RecruiterProfileRequest $profileRequest): JsonResponse
    {
        $this->authorize('update', $profileRequest);

        $profileRequest = $this->updateRequest->execute($profileRequest, $request->updatePayload());

        return BaseResponse::ok([
            'request' => new RecruiterProfileRequestResource($profileRequest),
        ])->toJsonResponse();
    }

    public function submit(RecruiterProfileRequest $profileRequest): JsonResponse
    {
        $this->authorize('update', $profileRequest);

        $profileRequest = $this->submitRequest->execute($profileRequest);

        return BaseResponse::ok([
            'request' => new RecruiterProfileRequestResource($profileRequest),
            'message' => $profileRequest->matched_count > 0
                ? __('Demande soumise — :count candidat(s) correspondant(s) trouvé(s).', ['count' => $profileRequest->matched_count])
                : __('Demande soumise — aucun candidat correspondant pour le moment.'),
        ])->toJsonResponse();
    }
}
