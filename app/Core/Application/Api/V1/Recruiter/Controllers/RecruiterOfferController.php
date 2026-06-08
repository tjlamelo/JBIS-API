<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\StoreRecruiterOfferSubmissionRequest;
use App\Core\Application\Api\V1\Recruiter\Requests\UpdateRecruiterOfferSubmissionRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterOfferSubmissionResource;
use App\Core\Domain\Recruiter\Actions\CreateRecruiterOfferSubmissionAction;
use App\Core\Domain\Recruiter\Actions\SubmitRecruiterOfferAction;
use App\Core\Domain\Recruiter\Actions\UpdateRecruiterOfferSubmissionAction;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecruiterOfferController extends Controller
{
    public function __construct(
        private readonly RecruiterAccess $access,
        private readonly CreateRecruiterOfferSubmissionAction $createOffer,
        private readonly UpdateRecruiterOfferSubmissionAction $updateOffer,
        private readonly SubmitRecruiterOfferAction $submitOffer,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RecruiterOfferSubmission::class);
        $organization = $this->access->primaryOrganization($request->user());
        if ($organization === null) {
            return BaseResponse::ok(['submissions' => [], 'meta' => ['total' => 0]])->toJsonResponse();
        }

        $submissions = RecruiterOfferSubmission::query()
            ->where('recruiter_organization_id', $organization->id)
            ->with(['submittedBy:id,name,email', 'reviewer:id,name'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
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

    public function store(StoreRecruiterOfferSubmissionRequest $request): JsonResponse
    {
        $this->authorize('create', RecruiterOfferSubmission::class);
        $organization = $this->access->primaryOrganization($request->user());
        if ($organization === null) {
            abort(403, __('Organisation recruteur introuvable.'));
        }

        $submission = $this->createOffer->execute(
            $organization,
            $request->user(),
            $request->offerPayload(),
        );

        return BaseResponse::created([
            'submission' => new RecruiterOfferSubmissionResource($submission),
        ])->toJsonResponse();
    }

    public function show(RecruiterOfferSubmission $submission): JsonResponse
    {
        $this->authorize('view', $submission);
        $submission->load(['organization', 'submittedBy:id,name,email', 'reviewer:id,name']);

        return BaseResponse::ok([
            'submission' => new RecruiterOfferSubmissionResource($submission),
        ])->toJsonResponse();
    }

    public function update(UpdateRecruiterOfferSubmissionRequest $request, RecruiterOfferSubmission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        $submission = $this->updateOffer->execute($submission, $request->offerPayload());

        return BaseResponse::ok([
            'submission' => new RecruiterOfferSubmissionResource($submission),
        ])->toJsonResponse();
    }

    public function submit(RecruiterOfferSubmission $submission): JsonResponse
    {
        $this->authorize('update', $submission);

        $submission = $this->submitOffer->execute($submission);

        return BaseResponse::ok([
            'submission' => new RecruiterOfferSubmissionResource($submission),
        ])->toJsonResponse();
    }
}
