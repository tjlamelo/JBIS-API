<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Partner\Requests\StorePartnerCohortStudentRequest;
use App\Core\Application\Api\V1\Partner\Resources\PartnerCohortStudentResource;
use App\Core\Domain\Partner\Actions\AddPartnerCohortStudentAction;
use App\Core\Domain\Partner\Actions\SyncPartnerCohortStudentChecklistAction;
use App\Core\Domain\Partner\Models\PartnerCohort;
use App\Core\Domain\Partner\Models\PartnerCohortStudent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerCohortStudentController extends Controller
{
    public function __construct(
        private readonly AddPartnerCohortStudentAction $addStudent,
        private readonly SyncPartnerCohortStudentChecklistAction $syncChecklist,
    ) {}

    public function index(Request $request, PartnerCohort $partnerCohort): JsonResponse
    {
        $this->authorize('view', $partnerCohort);

        $students = $partnerCohort->students()
            ->with(['student:id,name,email', 'documents', 'cohort.requiredDocuments'])
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 25));

        return BaseResponse::ok([
            'students' => PartnerCohortStudentResource::collection($students),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StorePartnerCohortStudentRequest $request, PartnerCohort $partnerCohort): JsonResponse
    {
        $this->authorize('update', $partnerCohort);

        $student = $this->addStudent->execute($partnerCohort, $request->user(), $request->validated());

        return BaseResponse::created([
            'student' => new PartnerCohortStudentResource($student),
        ])->toJsonResponse();
    }

    public function show(PartnerCohort $partnerCohort, PartnerCohortStudent $partnerCohortStudent): JsonResponse
    {
        $this->authorize('view', $partnerCohortStudent);
        abort_unless($partnerCohortStudent->partner_cohort_id === $partnerCohort->id, 404);

        $partnerCohortStudent->load(['student:id,name,email', 'documents.userDocument', 'cohort.requiredDocuments']);
        $this->syncChecklist->execute($partnerCohortStudent);
        $partnerCohortStudent->load(['documents.userDocument']);

        return BaseResponse::ok([
            'student' => new PartnerCohortStudentResource($partnerCohortStudent),
        ])->toJsonResponse();
    }

    public function refreshDocuments(PartnerCohort $partnerCohort, PartnerCohortStudent $partnerCohortStudent): JsonResponse
    {
        $this->authorize('view', $partnerCohortStudent);
        abort_unless($partnerCohortStudent->partner_cohort_id === $partnerCohort->id, 404);

        $student = $this->syncChecklist->execute($partnerCohortStudent);
        $student->load(['documents.userDocument', 'student:id,name,email', 'cohort.requiredDocuments']);

        return BaseResponse::ok([
            'student' => new PartnerCohortStudentResource($student),
        ])->toJsonResponse();
    }
}
