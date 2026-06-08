<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\ReviewRecruiterOfferSubmissionRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterOfferSubmissionResource;
use App\Core\Domain\Recruiter\Actions\ReviewRecruiterOfferSubmissionAction;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRecruiterOfferController extends Controller
{
    public function __construct(
        private readonly ReviewRecruiterOfferSubmissionAction $reviewSubmission,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterOfferSubmission::class);

        $submissions = RecruiterOfferSubmission::query()
            ->with(['organization:id,name,slug', 'submittedBy:id,name,email', 'reviewer:id,name'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('organization_id'), fn ($q, $orgId) => $q->where('recruiter_organization_id', $orgId))
            ->latest('submitted_at')
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'submissions' => RecruiterOfferSubmissionResource::collection($submissions),
            'meta' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'per_page' => $submissions->perPage(),
                'total' => $submissions->total(),
            ],
        ])->toJsonResponse();
    }

    public function show(RecruiterOfferSubmission $recruiterOfferSubmission): JsonResponse
    {
        $this->authorize('view', $recruiterOfferSubmission);
        $recruiterOfferSubmission->load(['organization', 'submittedBy:id,name,email', 'reviewer:id,name']);

        return BaseResponse::ok([
            'submission' => new RecruiterOfferSubmissionResource($recruiterOfferSubmission),
        ])->toJsonResponse();
    }

    public function review(
        ReviewRecruiterOfferSubmissionRequest $request,
        RecruiterOfferSubmission $recruiterOfferSubmission,
    ): JsonResponse {
        $this->authorize('review', $recruiterOfferSubmission);

        $submission = $this->reviewSubmission->execute(
            $recruiterOfferSubmission,
            $request->user(),
            $request->validated(),
        );

        return BaseResponse::ok([
            'submission' => new RecruiterOfferSubmissionResource($submission),
        ])->toJsonResponse();
    }
}
