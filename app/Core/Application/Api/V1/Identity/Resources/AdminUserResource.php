<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Resources;

use App\Core\Application\Api\V1\Identity\Support\ProfileResponseMapper;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
final class AdminUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ProfileResponseMapper $mapper */
        $mapper = app(ProfileResponseMapper::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_is_placeholder' => (bool) $this->email_is_placeholder,
            'phone_number1' => $this->phone_number1,
            'active' => (bool) $this->active,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'roles' => $this->relationLoaded('roles')
                ? $this->getRoleNames()->values()->all()
                : [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'profile' => $this->when(
                $this->relationLoaded('profile') && $this->profile !== null,
                fn () => $mapper->toArray($this->profile),
            ),
            'sectors' => $this->when(
                $this->relationLoaded('trades'),
                fn () => $this->sectorsFromTrades()->map(static fn ($sector) => [
                    'id' => $sector->id,
                    'slug' => $sector->slug,
                    'name' => $sector->getTranslations('name'),
                ])->values()->all(),
            ),
            'trades' => $this->when(
                $this->relationLoaded('trades'),
                fn () => $this->trades->map(static fn ($trade) => [
                    'id' => $trade->id,
                    'slug' => $trade->slug,
                    'name' => $trade->getTranslations('name'),
                    'years_of_experience' => $trade->pivot?->years_of_experience,
                ])->values()->all(),
            ),
        ];
    }
}
