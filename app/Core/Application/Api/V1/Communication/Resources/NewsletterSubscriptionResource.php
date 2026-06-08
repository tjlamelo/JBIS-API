<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Communication\Resources;

use App\Core\Domain\Communication\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NewsletterSubscription */
final class NewsletterSubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'language' => $this->language,
            'scope' => $this->scope?->value ?? $this->scope,
            'status' => $this->status?->value ?? $this->status,
            'subscribed_at' => $this->subscribed_at?->toIso8601String(),
            'unsubscribed_at' => $this->unsubscribed_at?->toIso8601String(),
        ];
    }
}
