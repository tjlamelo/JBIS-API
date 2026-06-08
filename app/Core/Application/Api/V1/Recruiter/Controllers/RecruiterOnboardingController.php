<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterOnboardingApplicationResource;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecruiterOnboardingController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $application = RecruiterOnboardingApplication::query()
            ->with(['documents', 'organization', 'reviewer:id,name'])
            ->where('applicant_user_id', $request->user()->id)
            ->latest('updated_at')
            ->first();

        if ($application === null) {
            return BaseResponse::ok(['application' => null])->toJsonResponse();
        }

        $this->authorize('view', $application);

        return BaseResponse::ok([
            'application' => new RecruiterOnboardingApplicationResource($application),
        ])->toJsonResponse();
    }
}
