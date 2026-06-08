<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Resources;

use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecruiterOrganization */
final class RecruiterOrganizationResource extends JsonResource
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
            'portal_host' => $this->portal_host,
            'api_host' => $this->api_host,
            'mailbox_email' => $this->mailbox_email,
            'provisioning_error' => $this->provisioning_error,
            'provisioned_at' => $this->provisioned_at?->toIso8601String(),
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
