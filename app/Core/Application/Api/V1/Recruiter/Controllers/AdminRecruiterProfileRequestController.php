<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\ReviewRecruiterProfileRequestRequest;
use App\Core\Application\Api\V1\Recruiter\Requests\TransmitRecruiterProfileRequestRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterProfileRequestResource;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Actions\MatchRecruiterProfileRequestAction;
use App\Core\Domain\Recruiter\Actions\ReviewRecruiterProfileRequestAction;
use App\Core\Domain\Recruiter\Actions\TransmitRecruiterProfileRequestAction;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminRecruiterProfileRequestController extends Controller
{
    public function __construct(
        private readonly MatchRecruiterProfileRequestAction $matchRequest,
        private readonly TransmitRecruiterProfileRequestAction $transmitRequest,
        private readonly ReviewRecruiterProfileRequestAction $reviewRequest,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterProfileRequest::class);

        $requests = RecruiterProfileRequest::query()
            ->with(['organization:id,name,slug', 'submittedBy:id,name,email'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('organization_id'), fn ($q, $orgId) => $q->where('recruiter_organization_id', $orgId))
            ->latest('submitted_at')
            ->latest('updated_at')
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'requests' => RecruiterProfileRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ])->toJsonResponse();
    }

    public function show(RecruiterProfileRequest $profileRequest): JsonResponse
    {
        $this->authorize('view', $profileRequest);
        $profileRequest->load([
            'organization',
            'submittedBy:id,name,email',
            'reviewer:id,name',
            'transmittedBy:id,name',
        ]);

        $matchedCandidates = $this->matchedCandidatesPreview($profileRequest);

        return BaseResponse::ok([
            'request' => new RecruiterProfileRequestResource($profileRequest),
            'matched_candidates' => $matchedCandidates,
        ])->toJsonResponse();
    }

    public function match(RecruiterProfileRequest $profileRequest): JsonResponse
    {
        $this->authorize('transmit', $profileRequest);

        $profileRequest = $this->matchRequest->execute($profileRequest);
        $matchedCandidates = $this->matchedCandidatesPreview($profileRequest);

        return BaseResponse::ok([
            'request' => new RecruiterProfileRequestResource($profileRequest),
            'matched_candidates' => $matchedCandidates,
            'message' => __(':count candidat(s) correspondant(s).', ['count' => $profileRequest->matched_count]),
        ])->toJsonResponse();
    }

    public function transmit(
        TransmitRecruiterProfileRequestRequest $request,
        RecruiterProfileRequest $profileRequest,
    ): JsonResponse {
        $this->authorize('transmit', $profileRequest);

        $result = $this->transmitRequest->execute(
            $profileRequest,
            $request->user(),
            $request->validated('candidate_user_ids'),
            $request->validated('note'),
            $request->validated('visible_sections'),
            $request->validated('masked_fields'),
        );

        return BaseResponse::ok([
            'request' => new RecruiterProfileRequestResource($result['request']),
            'bulk' => $result['bulk'],
            'message' => __(':count profil(s) transmis au recruteur.', ['count' => $result['bulk']['assigned_count']]),
        ])->toJsonResponse();
    }

    public function review(
        ReviewRecruiterProfileRequestRequest $request,
        RecruiterProfileRequest $profileRequest,
    ): JsonResponse {
        $this->authorize('review', $profileRequest);

        $profileRequest = $this->reviewRequest->execute(
            $profileRequest,
            $request->user(),
            $request->validated('decision'),
            $request->validated('staff_note'),
            $request->validated('rejection_reason'),
        );

        return BaseResponse::ok([
            'request' => new RecruiterProfileRequestResource($profileRequest),
        ])->toJsonResponse();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function matchedCandidatesPreview(RecruiterProfileRequest $profileRequest): array
    {
        $ids = array_map('intval', $profileRequest->matched_candidate_ids ?? []);
        if ($ids === []) {
            return [];
        }

        return User::query()
            ->with('profile:id,user_id,first_name,last_name,gender,is_approved,profile_type')
            ->whereIn('id', $ids)
            ->get()
            ->map(static fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile ? [
                    'first_name' => $user->profile->first_name,
                    'last_name' => $user->profile->last_name,
                    'gender' => $user->profile->gender,
                    'is_approved' => (bool) $user->profile->is_approved,
                    'profile_type' => $user->profile->profile_type,
                ] : null,
            ])
            ->values()
            ->all();
    }
}
