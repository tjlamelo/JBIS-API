<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Partner\Requests\StorePartnerCohortRequest;
use App\Core\Application\Api\V1\Partner\Requests\UpdatePartnerCohortRequest;
use App\Core\Application\Api\V1\Partner\Resources\PartnerCohortResource;
use App\Core\Domain\Partner\Actions\CreatePartnerCohortAction;
use App\Core\Domain\Partner\Actions\SubmitPartnerCohortAction;
use App\Core\Domain\Partner\Actions\UpdatePartnerCohortAction;
use App\Core\Domain\Partner\Models\PartnerCohort;
use App\Core\Domain\Partner\Support\PartnerAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerCohortController extends Controller
{
    public function __construct(
        private readonly PartnerAccess $access,
        private readonly CreatePartnerCohortAction $createCohort,
        private readonly UpdatePartnerCohortAction $updateCohort,
        private readonly SubmitPartnerCohortAction $submitCohort,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PartnerCohort::class);
        $organization = $this->access->primaryOrganization($request->user());
        if ($organization === null) {
            return BaseResponse::ok(['cohorts' => [], 'meta' => ['total' => 0]])->toJsonResponse();
        }

        $cohorts = PartnerCohort::query()
            ->where('partner_organization_id', $organization->id)
            ->withCount('students')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'cohorts' => PartnerCohortResource::collection($cohorts),
            'meta' => [
                'current_page' => $cohorts->currentPage(),
                'last_page' => $cohorts->lastPage(),
                'per_page' => $cohorts->perPage(),
                'total' => $cohorts->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StorePartnerCohortRequest $request): JsonResponse
    {
        $this->authorize('create', PartnerCohort::class);
        $organization = $this->access->primaryOrganization($request->user());
        if ($organization === null) {
            abort(403, __('Organisation partenaire introuvable.'));
        }

        $cohort = $this->createCohort->execute($organization, $request->user(), $request->validated());

        return BaseResponse::created([
            'cohort' => new PartnerCohortResource($cohort),
        ])->toJsonResponse();
    }

    public function show(PartnerCohort $partnerCohort): JsonResponse
    {
        $this->authorize('view', $partnerCohort);
        $partnerCohort->load(['organization', 'requiredDocuments', 'submittedBy:id,name,email']);
        $partnerCohort->loadCount('students');

        return BaseResponse::ok([
            'cohort' => new PartnerCohortResource($partnerCohort),
        ])->toJsonResponse();
    }

    public function update(UpdatePartnerCohortRequest $request, PartnerCohort $partnerCohort): JsonResponse
    {
        $this->authorize('update', $partnerCohort);

        $cohort = $this->updateCohort->execute($partnerCohort, $request->validated());

        return BaseResponse::ok([
            'cohort' => new PartnerCohortResource($cohort),
        ])->toJsonResponse();
    }

    public function submit(PartnerCohort $partnerCohort): JsonResponse
    {
        $this->authorize('update', $partnerCohort);

        $cohort = $this->submitCohort->execute($partnerCohort);

        return BaseResponse::ok([
            'cohort' => new PartnerCohortResource($cohort),
            'message' => __('Cohorte soumise à JBIS pour validation.'),
        ])->toJsonResponse();
    }
}
