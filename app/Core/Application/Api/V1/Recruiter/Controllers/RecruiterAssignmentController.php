<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Resources\AdminUserResource;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterAssignmentResource;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecruiterAssignmentController extends Controller
{
    public function __construct(private readonly RecruiterAccess $access) {}

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
        abort_unless($this->access->canViewCandidate($recruiter, $candidateUser->id), 403);

        $candidateUser->load(['profile', 'experiences', 'educations', 'documents']);

        return BaseResponse::ok([
            'candidate' => new AdminUserResource($candidateUser),
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
