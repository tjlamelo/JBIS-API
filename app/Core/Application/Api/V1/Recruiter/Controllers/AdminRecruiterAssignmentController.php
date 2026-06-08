<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\StoreRecruiterAssignmentRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterAssignmentResource;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Actions\AssignProfileToRecruiterAction;
use App\Core\Domain\Recruiter\Actions\RevokeRecruiterAssignmentAction;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRecruiterAssignmentController extends Controller
{
    public function __construct(
        private readonly AssignProfileToRecruiterAction $assignProfile,
        private readonly RevokeRecruiterAssignmentAction $revokeAssignment,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterProfileAssignment::class);

        $assignments = RecruiterProfileAssignment::query()
            ->with(['candidate.profile', 'organization:id,name,slug', 'assignedBy:id,name'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('organization_id'), fn ($q, $orgId) => $q->where('recruiter_organization_id', $orgId))
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

    public function store(StoreRecruiterAssignmentRequest $request): JsonResponse
    {
        $this->authorize('create', RecruiterProfileAssignment::class);

        $organization = RecruiterOrganization::query()->findOrFail((int) $request->validated('recruiter_organization_id'));
        $candidate = User::query()->findOrFail((int) $request->validated('candidate_user_id'));

        $assignment = $this->assignProfile->execute(
            $organization,
            $candidate,
            $request->user(),
            $request->validated('note'),
        );

        $assignment->load(['candidate.profile', 'organization', 'assignedBy:id,name']);

        return BaseResponse::created([
            'assignment' => new RecruiterAssignmentResource($assignment),
        ])->toJsonResponse();
    }

    public function destroy(RecruiterProfileAssignment $recruiterAssignment): JsonResponse
    {
        $this->authorize('update', $recruiterAssignment);

        $assignment = $this->revokeAssignment->execute($recruiterAssignment);

        return BaseResponse::ok([
            'assignment' => new RecruiterAssignmentResource($assignment),
        ])->toJsonResponse();
    }
}
