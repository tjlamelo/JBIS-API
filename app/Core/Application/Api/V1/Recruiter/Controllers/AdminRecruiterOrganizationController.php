<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\StoreRecruiterOrganizationRequest;
use App\Core\Application\Api\V1\Recruiter\Requests\UpdateRecruiterOrganizationRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterOrganizationResource;
use App\Core\Domain\Recruiter\Actions\CreateRecruiterOrganizationAction;
use App\Core\Domain\Recruiter\Jobs\ProvisionRecruiterInfrastructureJob;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRecruiterOrganizationController extends Controller
{
    public function __construct(
        private readonly CreateRecruiterOrganizationAction $createOrganization,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterOrganization::class);

        $organizations = RecruiterOrganization::query()
            ->withCount('members')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'organizations' => RecruiterOrganizationResource::collection($organizations),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'per_page' => $organizations->perPage(),
                'total' => $organizations->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreRecruiterOrganizationRequest $request): JsonResponse
    {
        $this->authorize('create', RecruiterOrganization::class);

        $organization = $this->createOrganization->execute($request->validated());

        return BaseResponse::created([
            'organization' => new RecruiterOrganizationResource($organization),
        ])->toJsonResponse();
    }

    public function show(RecruiterOrganization $recruiterOrganization): JsonResponse
    {
        $this->authorize('view', $recruiterOrganization);
        $recruiterOrganization->load('members:id,name,email');

        return BaseResponse::ok([
            'organization' => new RecruiterOrganizationResource($recruiterOrganization),
        ])->toJsonResponse();
    }

    public function update(UpdateRecruiterOrganizationRequest $request, RecruiterOrganization $recruiterOrganization): JsonResponse
    {
        $this->authorize('update', $recruiterOrganization);
        $recruiterOrganization->fill($request->validated());
        $recruiterOrganization->save();
        $recruiterOrganization->load('members:id,name,email');

        return BaseResponse::ok([
            'organization' => new RecruiterOrganizationResource($recruiterOrganization),
        ])->toJsonResponse();
    }

    public function provision(RecruiterOrganization $recruiterOrganization): JsonResponse
    {
        $this->authorize('update', $recruiterOrganization);

        ProvisionRecruiterInfrastructureJob::dispatch($recruiterOrganization->id);

        return BaseResponse::ok([
            'message' => __('Provisioning démarré.'),
            'organization' => new RecruiterOrganizationResource($recruiterOrganization->fresh()),
        ])->toJsonResponse();
    }
}
