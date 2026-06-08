<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\ReviewRecruiterSubmissionRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterSubmissionResource;
use App\Core\Domain\Recruiter\Actions\ReviewRecruiterSubmissionAction;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRecruiterSubmissionController extends Controller
{
    public function __construct(
        private readonly ReviewRecruiterSubmissionAction $reviewSubmission,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterProfileSubmission::class);

        $submissions = RecruiterProfileSubmission::query()
            ->with(['candidate.profile', 'organization:id,name,slug', 'submittedBy:id,name,email', 'reviewer:id,name'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('organization_id'), fn ($q, $orgId) => $q->where('recruiter_organization_id', $orgId))
            ->latest('submitted_at')
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'submissions' => RecruiterSubmissionResource::collection($submissions),
            'meta' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'per_page' => $submissions->perPage(),
                'total' => $submissions->total(),
            ],
        ])->toJsonResponse();
    }

    public function show(RecruiterProfileSubmission $recruiterSubmission): JsonResponse
    {
        $this->authorize('view', $recruiterSubmission);
        $recruiterSubmission->load(['candidate.profile', 'organization', 'submittedBy:id,name,email', 'reviewer:id,name']);

        return BaseResponse::ok([
            'submission' => new RecruiterSubmissionResource($recruiterSubmission),
        ])->toJsonResponse();
    }

    public function review(ReviewRecruiterSubmissionRequest $request, RecruiterProfileSubmission $recruiterSubmission): JsonResponse
    {
        $this->authorize('review', $recruiterSubmission);

        $submission = $this->reviewSubmission->execute(
            $recruiterSubmission,
            $request->user(),
            $request->validated(),
        );

        return BaseResponse::ok([
            'submission' => new RecruiterSubmissionResource($submission),
        ])->toJsonResponse();
    }
}
