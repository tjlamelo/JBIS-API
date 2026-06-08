<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterOrganizationResource;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RecruiterOrganizationController extends Controller
{
    public function __construct(private readonly RecruiterAccess $access) {}

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $organization = $request->attributes->get('recruiterOrganization')
            ?? $this->access->primaryOrganization($user);

        abort_if($organization === null, 404, 'Aucune organisation recruteur.');

        $this->authorize('view', $organization);
        $organization->load('members:id,name,email');

        return BaseResponse::ok([
            'organization' => new RecruiterOrganizationResource($organization),
        ])->toJsonResponse();
    }
}
