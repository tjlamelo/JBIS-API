<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\ReviewRecruiterOnboardingApplicationRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterOnboardingApplicationResource;
use App\Core\Domain\Recruiter\Actions\ReviewRecruiterOnboardingApplicationAction;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRecruiterOnboardingController extends Controller
{
    public function __construct(
        private readonly ReviewRecruiterOnboardingApplicationAction $reviewApplication,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterOnboardingApplication::class);

        $applications = RecruiterOnboardingApplication::query()
            ->with(['applicant:id,name,email', 'organization:id,name,slug,status', 'reviewer:id,name', 'documents'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('submitted_at')
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'applications' => RecruiterOnboardingApplicationResource::collection($applications),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ])->toJsonResponse();
    }

    public function show(RecruiterOnboardingApplication $recruiterOnboardingApplication): JsonResponse
    {
        $this->authorize('view', $recruiterOnboardingApplication);
        $recruiterOnboardingApplication->load(['applicant', 'organization', 'reviewer:id,name', 'documents']);

        return BaseResponse::ok([
            'application' => new RecruiterOnboardingApplicationResource($recruiterOnboardingApplication),
        ])->toJsonResponse();
    }

    public function review(
        ReviewRecruiterOnboardingApplicationRequest $request,
        RecruiterOnboardingApplication $recruiterOnboardingApplication,
    ): JsonResponse {
        $this->authorize('review', $recruiterOnboardingApplication);

        $application = $this->reviewApplication->execute(
            $recruiterOnboardingApplication,
            $request->user(),
            $request->validated(),
        );

        return BaseResponse::ok([
            'application' => new RecruiterOnboardingApplicationResource($application),
        ])->toJsonResponse();
    }
}
