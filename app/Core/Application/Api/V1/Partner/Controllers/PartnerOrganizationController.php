<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Partner\Resources\PartnerOrganizationResource;
use App\Core\Domain\Partner\Support\PartnerAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerOrganizationController extends Controller
{
    public function __construct(private readonly PartnerAccess $access) {}

    public function me(Request $request): JsonResponse
    {
        $organization = $this->access->primaryOrganization($request->user());
        if ($organization === null) {
            return BaseResponse::ok(['organization' => null])->toJsonResponse();
        }

        $organization->load('members:id,name,email');

        return BaseResponse::ok([
            'organization' => new PartnerOrganizationResource($organization),
        ])->toJsonResponse();
    }
}
