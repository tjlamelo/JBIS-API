<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Certification\StoreCertificationRequest;
use App\Core\Application\Api\V1\Identity\Requests\Certification\UpdateCertificationRequest;
use App\Core\Application\Api\V1\Identity\Requests\ValidateIdentityRecordStatusRequest;
use App\Core\Application\Api\V1\Identity\Resources\CertificationResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Actions\Certification\DeleteCertificationAction;
use App\Core\Domain\Identity\Actions\Certification\StoreCertificationAction;
use App\Core\Domain\Identity\Actions\Certification\UpdateCertificationAction;
use App\Core\Domain\Identity\Actions\Certification\ValidateCertificationAction;
use App\Core\Domain\Identity\Models\Certification;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CertificationController extends Controller
{
    use ScopesUserOwnedIndex;

    public function __construct(

        private readonly StoreCertificationAction $storeCertification,

        private readonly UpdateCertificationAction $updateCertification,

        private readonly DeleteCertificationAction $deleteCertification,

        private readonly ValidateCertificationAction $validateCertification,

    ) {}

    public function index(Request $request): JsonResponse
    {

        $this->authorize('viewAny', Certification::class);

        $query = Certification::query()->with(['document']);

        $this->scopeIndexToUser($request, $query, 'certification');

        if ($request->filled('status')) {

            $query->where('status', (string) $request->input('status'));

        }

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([

            'data' => CertificationResource::collection($items->items()),

            'meta' => [

                'current_page' => $items->currentPage(),

                'last_page' => $items->lastPage(),

                'per_page' => $items->perPage(),

                'total' => $items->total(),

            ],

        ])->toJsonResponse();

    }

    public function store(StoreCertificationRequest $request): JsonResponse
    {

        $targetUser = User::query()->findOrFail($request->targetUserId());

        $certification = $this->storeCertification->execute($targetUser, $request->safe()->except(['user_id']));

        return BaseResponse::created([

            'message' => __('Certification enregistrée.'),

            'certification' => new CertificationResource($certification->load(['document'])),

        ])->toJsonResponse();

    }

    public function show(Request $request, Certification $certification): JsonResponse
    {

        $this->authorize('view', $certification);

        return BaseResponse::ok([

            'certification' => new CertificationResource($certification->load(['document'])),

        ])->toJsonResponse();

    }

    public function update(UpdateCertificationRequest $request, Certification $certification): JsonResponse
    {

        $certification = $this->updateCertification->execute($certification, $request->validated());

        return BaseResponse::ok([

            'message' => __('Certification mise à jour.'),

            'certification' => new CertificationResource($certification),

        ])->toJsonResponse();

    }

    public function destroy(Request $request, Certification $certification): JsonResponse
    {

        $this->authorize('delete', $certification);

        $this->deleteCertification->execute($certification);

        return BaseResponse::ok(['message' => __('Certification supprimée.')])->toJsonResponse();

    }

    public function validateItem(ValidateIdentityRecordStatusRequest $request, Certification $certification): JsonResponse
    {

        $this->authorize('validate', $certification);

        $certification = $this->validateCertification->execute(

            $certification,

            (string) $request->input('status'),

            (int) $request->user()?->id,

        );

        return BaseResponse::ok([

            'message' => __('Statut de la certification mis à jour.'),

            'certification' => new CertificationResource($certification),

        ])->toJsonResponse();

    }

}
