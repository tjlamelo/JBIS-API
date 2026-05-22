<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\UpdateUserSettingsRequest;
use App\Core\Application\Api\V1\Identity\Support\UserSettingsPayloadMapper;
use App\Core\Domain\Identity\Actions\Settings\EnsureUserSettingsAction;
use App\Core\Domain\Identity\Actions\Settings\UpdateUserSettingsAction;
use App\Core\Domain\Identity\DTOs\UserSettingsDto;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MySettingsController extends Controller
{
    public function __construct(
        private readonly EnsureUserSettingsAction $ensureUserSettings,
        private readonly UpdateUserSettingsAction $updateUserSettings,
        private readonly UserSettingsPayloadMapper $settingsMapper,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $settings = $this->ensureUserSettings->execute($request->user());

        return BaseResponse::ok([
            'settings' => $this->settingsMapper->toArray($settings),
        ])->toJsonResponse();
    }

    public function update(UpdateUserSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['provided_keys'] = array_keys($validated);

        $settings = $this->updateUserSettings->execute(
            $request->user(),
            UserSettingsDto::fromArray($validated),
        );

        return BaseResponse::ok([
            'message' => __('Paramètres mis à jour.'),
            'settings' => $this->settingsMapper->toArray($settings),
        ])->toJsonResponse();
    }
}
