<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\Offer\OfferIndexQuery;
use App\Core\Application\Api\V1\Catalog\Requests\Offer\StoreOfferRequest;
use App\Core\Application\Api\V1\Catalog\Requests\Offer\UpdateOfferRequest;
use App\Core\Application\Api\V1\Catalog\Resources\Offer\OfferResource;
use App\Core\Domain\Catalog\Actions\Offer\CreateOfferAction;
use App\Core\Domain\Catalog\Actions\Offer\DeleteOfferAction;
use App\Core\Domain\Catalog\Actions\Offer\UpdateOfferAction;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Shared\Media\Actions\StoreMediaAction;
use App\Core\Domain\Shared\Media\Support\MediaUrlResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOfferController extends Controller
{
    public function __construct(
        private readonly OfferIndexQuery $offerIndexQuery,
        private readonly CreateOfferAction $createOfferAction,
        private readonly UpdateOfferAction $updateOfferAction,
        private readonly DeleteOfferAction $deleteOfferAction,
        private readonly StoreMediaAction $storeMediaAction,
        private readonly MediaUrlResolver $mediaUrlResolver,
    ) {}

    public function index(OfferIndexQuery $query): JsonResponse
    {
        // On s'assure que la Query charge les relations nécessaires pour la table
        $offers = $query->with(['company', 'country', 'city', 'trade.category', 'contractType', 'offerType', 'workSchedule', 'educationLevel', 'benefits', 'languages', 'skills.category', 'requiredDocuments'])
            ->paginate(request()->integer('per_page', 15));

        return BaseResponse::ok([
            'offers' => OfferResource::collection($offers),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
                'total' => $offers->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreOfferRequest $request): JsonResponse
    {
        $offer = $this->createOfferAction->execute($request->toDto());

        return BaseResponse::created([
            'message' => __('Offre d emploi creee avec succes.'),
            'offer' => new OfferResource($offer),
        ])->toJsonResponse();
    }

    public function show(Offer $offer): JsonResponse
    {
        // IMPORTANT : On charge les relations pour que le frontend JBIS ait accès
        // à offer.company.name, offer.category.name, etc.
        $offer->load(['company', 'country', 'city', 'trade.category', 'program', 'contractType', 'offerType', 'workSchedule', 'educationLevel', 'benefits', 'languages', 'skills.category', 'requiredDocuments']);

        return BaseResponse::ok([
            'offer' => new OfferResource($offer),
        ])->toJsonResponse();
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'photo' => ['required_without:file,image', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
            'file' => ['required_without:photo,image', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
            'image' => ['required_without:photo,file', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $file = $validated['photo'] ?? $validated['file'] ?? $validated['image'];

        $uploaded = $this->storeMediaAction->execute(
            $file,
            'catalog/offers/flyers'
        );

        $media = $uploaded->toArray();
        $urls = $this->mediaUrlResolver->all($media);

        return BaseResponse::ok([
            'message' => __('Photo telechargee avec succes.'),
            'photo' => $urls,
            'media' => $media,
        ])->toJsonResponse();
    }

    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse
    {
        $updated = $this->updateOfferAction->execute($offer->id, $request->toDto());

        return BaseResponse::ok([
            'message' => __('Offre d emploi mise a jour avec succes.'),
            'offer' => new OfferResource($updated),
        ])->toJsonResponse();
    }

    public function destroy(Offer $offer): JsonResponse
    {
        $this->deleteOfferAction->execute($offer->id);

        return BaseResponse::ok([
            'message' => __('Offre d emploi supprimee avec succes.'),
        ])->toJsonResponse();
    }
}
