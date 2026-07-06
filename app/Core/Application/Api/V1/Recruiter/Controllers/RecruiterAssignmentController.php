<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterAssignmentResource;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use App\Core\Domain\Recruiter\Support\RecruiterSharedCandidatePresenter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecruiterAssignmentController extends Controller
{
    public function __construct(
        private readonly RecruiterAccess $access,
        private readonly RecruiterSharedCandidatePresenter $presenter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterProfileAssignment::class);
        $organization = $this->resolveOrganization($request);

        $assignments = RecruiterProfileAssignment::query()
            ->where('recruiter_organization_id', $organization->id)
            ->where('status', RecruiterAssignmentStatus::Active)
            ->with(['candidate.profile', 'assignedBy:id,name'])
            ->latest('assigned_at')
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'assignments' => RecruiterAssignmentResource::collection($assignments),
            'meta' => [
                'current_page' => $assignments->currentPage(),
                'last_page' => $assignments->lastPage(),
                'per_page' => $assignments->perPage(),
                'total' => $assignments->total(),
            ],
        ])->toJsonResponse();
    }

    public function show(Request $request, User $candidateUser): JsonResponse
    {
        $recruiter = $request->user();
        abort_if($recruiter === null, 401);

        $assignment = $this->access->activeAssignment($recruiter, $candidateUser->id);
        abort_if($assignment === null, 403);

        $visibleSections = $assignment->resolvedVisibleSections();
        $candidateUser->load($this->presenter->eagerLoadsFor($visibleSections));

        return BaseResponse::ok([
            'candidate' => $this->presenter->present($candidateUser, $visibleSections),
            'visible_sections' => $visibleSections,
            'assignment' => new RecruiterAssignmentResource($assignment->load(['assignedBy:id,name'])),
        ])->toJsonResponse();
    }

    private function resolveOrganization(Request $request): \App\Core\Domain\Recruiter\Models\RecruiterOrganization
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $organization = $request->attributes->get('recruiterOrganization')
            ?? $this->access->primaryOrganization($user);

        abort_if($organization === null, 403);

        return $organization;
    }
}
