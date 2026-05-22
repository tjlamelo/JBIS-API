<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\StoreUserConsentRequest;
use App\Core\Application\Api\V1\Identity\Resources\UserConsentResource;
use App\Core\Domain\Identity\Actions\Consent\RecordUserConsentAction;
use App\Core\Domain\Identity\Actions\Consent\ResolveUserConsentStatusAction;
use App\Core\Domain\Identity\Models\UserConsent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MyConsentController extends Controller
{
    public function __construct(
        private readonly ResolveUserConsentStatusAction $resolveConsentStatus,
        private readonly RecordUserConsentAction $recordUserConsent,
    ) {}

    public function status(Request $request): JsonResponse
    {
        return BaseResponse::ok([
            'consents' => $this->resolveConsentStatus->execute($request->user()),
        ])->toJsonResponse();
    }

    public function history(Request $request): JsonResponse
    {
        $items = UserConsent::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('accepted_at')
            ->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([
            'consents' => UserConsentResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreUserConsentRequest $request): JsonResponse
    {
        $consent = $this->recordUserConsent->execute(
            $request->user(),
            (string) $request->input('type'),
            (string) $request->input('version'),
            $request->ip(),
            $request->userAgent(),
        );

        return BaseResponse::created([
            'message' => __('Consentement enregistré.'),
            'consent' => new UserConsentResource($consent),
            'consents' => $this->resolveConsentStatus->execute($request->user()),
        ])->toJsonResponse();
    }
}
