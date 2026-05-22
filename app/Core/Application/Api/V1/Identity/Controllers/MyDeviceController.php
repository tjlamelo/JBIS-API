<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Resources\UserDeviceResource;
use App\Core\Domain\Identity\Actions\Device\RevokeUserDeviceAction;
use App\Core\Domain\Identity\Models\UserDevice;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MyDeviceController extends Controller
{
    public function __construct(
        private readonly RevokeUserDeviceAction $revokeUserDevice,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $devices = UserDevice::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_seen_at')
            ->get();

        return BaseResponse::ok([
            'devices' => UserDeviceResource::collection($devices),
        ])->toJsonResponse();
    }

    public function destroy(Request $request, UserDevice $device): JsonResponse
    {
        if ((int) $device->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        try {
            $this->revokeUserDevice->execute(
                $request->user(),
                $device->id,
                $request->user()->currentAccessToken()?->id,
            );
        } catch (\InvalidArgumentException $exception) {
            return BaseResponse::badRequest([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'message' => __('Appareil déconnecté.'),
        ])->toJsonResponse();
    }
}
