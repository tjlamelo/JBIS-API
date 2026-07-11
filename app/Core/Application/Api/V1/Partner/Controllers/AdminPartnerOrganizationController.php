<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Partner\Requests\StorePartnerOrganizationRequest;
use App\Core\Application\Api\V1\Partner\Resources\PartnerOrganizationResource;
use App\Core\Domain\Partner\Actions\CreatePartnerOrganizationAction;
use App\Core\Domain\Partner\Models\PartnerOrganization;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminPartnerOrganizationController extends Controller
{
    public function __construct(
        private readonly CreatePartnerOrganizationAction $createOrganization,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PartnerOrganization::class);

        $organizations = PartnerOrganization::query()
            ->withCount('members')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        return BaseResponse::ok([
            'organizations' => PartnerOrganizationResource::collection($organizations),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'per_page' => $organizations->perPage(),
                'total' => $organizations->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StorePartnerOrganizationRequest $request): JsonResponse
    {
        $this->authorize('create', PartnerOrganization::class);

        $organization = $this->createOrganization->execute($request->validated());

        return BaseResponse::created([
            'organization' => new PartnerOrganizationResource($organization),
        ])->toJsonResponse();
    }

    public function show(PartnerOrganization $partnerOrganization): JsonResponse
    {
        $this->authorize('view', $partnerOrganization);
        $partnerOrganization->load('members:id,name,email');

        return BaseResponse::ok([
            'organization' => new PartnerOrganizationResource($partnerOrganization),
        ])->toJsonResponse();
    }
}
