<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Resources\UserConsentResource;
use App\Core\Application\Api\V1\Identity\Support\UserSettingsPayloadMapper;
use App\Core\Domain\Identity\Actions\Consent\ResolveUserConsentStatusAction;
use App\Core\Domain\Identity\Actions\Settings\EnsureUserSettingsAction;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserConsent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminUserDossierController extends Controller
{
    public function __construct(
        private readonly ResolveUserConsentStatusAction $resolveConsentStatus,
        private readonly EnsureUserSettingsAction $ensureUserSettings,
        private readonly UserSettingsPayloadMapper $settingsMapper,
    ) {}

    public function consents(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $history = UserConsent::query()
            ->where('user_id', $user->id)
            ->orderByDesc('accepted_at')
            ->paginate((int) $request->integer('per_page', 50));

        return BaseResponse::ok([
            'status' => $this->resolveConsentStatus->execute($user),
            'history' => UserConsentResource::collection($history->items()),
            'meta' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'total' => $history->total(),
            ],
        ])->toJsonResponse();
    }

    public function settings(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $settings = $this->ensureUserSettings->execute($user);

        return BaseResponse::ok([
            'settings' => $this->settingsMapper->toArray($settings),
        ])->toJsonResponse();
    }
}
