<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Domain\Identity\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserDevice */
class UserDeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentTokenId = $request->user()?->currentAccessToken()?->id;

        return [
            'id' => $this->id,
            'device_name' => $this->device_name,
            'ip' => $this->ip,
            'last_ip' => $this->last_ip,
            'user_agent' => $this->user_agent,
            'is_trusted' => (bool) $this->is_trusted,
            'is_current' => $currentTokenId !== null
                && (int) $this->personal_access_token_id === (int) $currentTokenId,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'first_seen_at' => $this->first_seen_at?->toIso8601String(),
            'login_count' => (int) $this->login_count,
        ];
    }
}
