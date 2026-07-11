<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Partner\Resources;

use App\Core\Domain\Partner\Models\PartnerOrganization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PartnerOrganization */
final class PartnerOrganizationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status?->value ?? $this->status,
            'company_id' => $this->company_id,
            'members' => $this->whenLoaded('members', fn () => $this->members->map(static fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_owner' => (bool) $user->pivot?->is_owner,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
