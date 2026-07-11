<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Partner\Requests\ReviewPartnerCohortRequest;
use App\Core\Application\Api\V1\Partner\Resources\PartnerCohortResource;
use App\Core\Domain\Partner\Actions\ReviewPartnerCohortAction;
use App\Core\Domain\Partner\Models\PartnerCohort;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminPartnerCohortController extends Controller
{
    public function __construct(
        private readonly ReviewPartnerCohortAction $reviewCohort,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PartnerCohort::class);

        $cohorts = PartnerCohort::query()
            ->with(['organization:id,name,slug'])
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

    public function show(PartnerCohort $partnerCohort): JsonResponse
    {
        $this->authorize('view', $partnerCohort);
        $partnerCohort->load(['organization', 'requiredDocuments', 'submittedBy:id,name,email', 'reviewer:id,name']);
        $partnerCohort->loadCount('students');

        return BaseResponse::ok([
            'cohort' => new PartnerCohortResource($partnerCohort),
        ])->toJsonResponse();
    }

    public function review(ReviewPartnerCohortRequest $request, PartnerCohort $partnerCohort): JsonResponse
    {
        $this->authorize('review', $partnerCohort);

        $cohort = $this->reviewCohort->execute(
            $partnerCohort,
            $request->user(),
            (string) $request->validated('decision'),
            $request->validated('staff_note'),
            $request->validated('rejection_reason'),
        );

        return BaseResponse::ok([
            'cohort' => new PartnerCohortResource($cohort),
        ])->toJsonResponse();
    }
}
