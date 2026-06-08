<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\CreateRecruiterSubmissionRequest;
use App\Core\Application\Api\V1\Recruiter\Requests\UpdateRecruiterSubmissionStepRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterSubmissionResource;
use App\Core\Domain\Recruiter\Actions\CreateRecruiterSubmissionAction;
use App\Core\Domain\Recruiter\Actions\SubmitRecruiterProfileAction;
use App\Core\Domain\Recruiter\Actions\UpdateRecruiterSubmissionStepAction;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecruiterSubmissionController extends Controller
{
    public function __construct(
        private readonly RecruiterAccess $access,
        private readonly CreateRecruiterSubmissionAction $createSubmission,
        private readonly UpdateRecruiterSubmissionStepAction $updateStep,
        private readonly SubmitRecruiterProfileAction $submitProfile,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterProfileSubmission::class);
        $organization = $this->resolveOrganization($request);

        $submissions = RecruiterProfileSubmission::query()
            ->where('recruiter_organization_id', $organization->id)
            ->with(['candidate.profile', 'submittedBy:id,name,email', 'reviewer:id,name'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
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

    public function store(CreateRecruiterSubmissionRequest $request): JsonResponse
    {
        $this->authorize('create', RecruiterProfileSubmission::class);
        $organization = $this->resolveOrganization($request);

        $submission = $this->createSubmission->execute(
            $organization,
            $request->user(),
            $request->candidatePayload(),
        );

        return BaseResponse::created([
            'submission' => new RecruiterSubmissionResource($submission),
        ])->toJsonResponse();
    }

    public function show(Request $request, RecruiterProfileSubmission $submission): JsonResponse
    {
        $this->authorize('view', $submission);
        $submission->load(['candidate.profile', 'organization', 'submittedBy:id,name,email', 'reviewer:id,name']);

        return BaseResponse::ok([
            'submission' => new RecruiterSubmissionResource($submission),
        ])->toJsonResponse();
    }

    public function updateStep(UpdateRecruiterSubmissionStepRequest $request, RecruiterProfileSubmission $submission, string $step): JsonResponse
    {
        $this->authorize('update', $submission);
        $profile = $this->updateStep->execute($submission, $step, $request->validated());
        $submission->load(['candidate.profile', 'organization']);

        return BaseResponse::ok([
            'submission' => new RecruiterSubmissionResource($submission),
            'profile' => $profile,
        ])->toJsonResponse();
    }

    public function submit(Request $request, RecruiterProfileSubmission $submission): JsonResponse
    {
        $this->authorize('update', $submission);
        $submission = $this->submitProfile->execute($submission);

        return BaseResponse::ok([
            'submission' => new RecruiterSubmissionResource($submission),
        ])->toJsonResponse();
    }

    private function resolveOrganization(Request $request): \App\Core\Domain\Recruiter\Models\RecruiterOrganization
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $organization = $request->attributes->get('recruiterOrganization')
            ?? $this->access->primaryOrganization($user);

        abort_if($organization === null, 403, 'Organisation recruteur requise.');

        return $organization;
    }
}
