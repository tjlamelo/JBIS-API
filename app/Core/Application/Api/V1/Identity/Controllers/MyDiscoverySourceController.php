<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\StoreUserDiscoverySourceRequest;
use App\Core\Domain\Identity\Actions\Profile\SaveUserDiscoverySourceAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class MyDiscoverySourceController extends Controller
{
    public function __construct(
        private readonly SaveUserDiscoverySourceAction $saveDiscoverySource,
    ) {}

    public function store(StoreUserDiscoverySourceRequest $request): JsonResponse
    {
        try {
            $result = $this->saveDiscoverySource->execute(
                $request->user(),
                (int) $request->input('discovery_source_id'),
                $request->input('discovery_source_other'),
            );
        } catch (ValidationException $exception) {
            return BaseResponse::unprocessableEntity(
                ['errors' => $exception->errors()],
                $exception->getMessage(),
            )->toJsonResponse();
        }

        $profile = $result['profile'];

        return BaseResponse::ok([
            'discovery_source' => [
                'id' => $profile->discovery_source_id,
                'other' => $profile->discovery_source_other,
                'key' => $profile->discoverySource?->key,
                'label' => $profile->discoverySource?->label,
            ],
        ])->toJsonResponse();
    }
}
