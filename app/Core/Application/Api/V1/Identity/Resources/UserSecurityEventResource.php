<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Domain\Identity\Models\UserSecurityEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserSecurityEvent */
final class UserSecurityEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_device_id' => $this->user_device_id,
            'event' => $this->event,
            'risk_score' => $this->risk_score,
            'risk_level' => $this->risk_level,
            'signals' => $this->signals,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'meta' => $this->meta,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null),
            'device' => $this->whenLoaded('device', fn () => $this->device ? [
                'id' => $this->device->id,
                'device_name' => $this->device->device_name,
                'device_key' => $this->device->device_key,
                'ip' => $this->device->ip ?? $this->device->last_ip,
            ] : null),
        ];
    }
}
