<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\CertificationOffer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Requests\CertificationOffer\StoreCertificationOfferRequest;
use App\Core\Application\Api\V1\Catalog\Requests\CertificationOffer\UpdateCertificationOfferRequest;
use App\Core\Application\Api\V1\Catalog\Resources\CertificationOffer\CertificationOfferResource;
use App\Core\Domain\Catalog\Actions\CertificationOffer\CreateCertificationOfferAction;
use App\Core\Domain\Catalog\Actions\CertificationOffer\DeleteCertificationOfferAction;
use App\Core\Domain\Catalog\Actions\CertificationOffer\UpdateCertificationOfferAction;
use App\Core\Domain\Catalog\Models\CertificationOffer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminCertificationOfferController extends Controller
{
    public function __construct(
        private readonly CreateCertificationOfferAction $createOffer,
        private readonly UpdateCertificationOfferAction $updateOffer,
        private readonly DeleteCertificationOfferAction $deleteOffer,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CertificationOffer::class);

        $search = trim((string) $request->query('search', ''));
        $active = $request->query('is_active');

        $offers = CertificationOffer::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $like = "%{$search}%";
                    $q->where('title_fr', 'like', $like)
                        ->orWhere('title_en', 'like', $like)
                        ->orWhere('domain', 'like', $like);
                });
            })
            ->when($active === '1' || $active === 'true', fn ($q) => $q->where('is_active', true))
            ->when($active === '0' || $active === 'false', fn ($q) => $q->where('is_active', false))
            ->orderBy('sort_order')
            ->orderBy('title_fr')
            ->paginate(min(100, max(1, (int) $request->query('per_page', 20))));

        return BaseResponse::ok([
            'certification_offers' => CertificationOfferResource::collection($offers),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
                'per_page' => $offers->perPage(),
                'total' => $offers->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreCertificationOfferRequest $request): JsonResponse
    {
        $offer = $this->createOffer->execute($request->toDto());

        return BaseResponse::created([
            'message' => __('Certification catalogue créée.'),
            'certification_offer' => new CertificationOfferResource($offer),
        ])->toJsonResponse();
    }

    public function show(CertificationOffer $certificationOffer): JsonResponse
    {
        $this->authorize('view', $certificationOffer);

        return BaseResponse::ok([
            'certification_offer' => new CertificationOfferResource($certificationOffer),
        ])->toJsonResponse();
    }

    public function update(UpdateCertificationOfferRequest $request, CertificationOffer $certificationOffer): JsonResponse
    {
        $offer = $this->updateOffer->execute($certificationOffer->id, $request->toDto());

        return BaseResponse::ok([
            'message' => __('Certification catalogue mise à jour.'),
            'certification_offer' => new CertificationOfferResource($offer),
        ])->toJsonResponse();
    }

    public function destroy(CertificationOffer $certificationOffer): JsonResponse
    {
        $this->authorize('delete', $certificationOffer);
        $this->deleteOffer->execute($certificationOffer->id);

        return BaseResponse::ok([
            'message' => __('Certification catalogue supprimée.'),
        ])->toJsonResponse();
    }
}
