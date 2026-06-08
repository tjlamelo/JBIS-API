<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;

final class DispatchOfferNewslettersAction
{
    public function __construct(
        private readonly SendOfferNewsletterAction $sendNewsletter,
    ) {}

    /**
     * @return array{sent: int, skipped: int, total: int}
     */
    public function execute(?int $limit = null): array
    {
        $sent = 0;
        $skipped = 0;

        $query = NewsletterSubscription::query()
            ->where('status', NewsletterSubscriptionStatus::Subscribed)
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $subscriptions = $query->get();
        $total = $subscriptions->count();

        foreach ($subscriptions as $subscription) {
            if ($this->sendNewsletter->execute($subscription)) {
                $sent++;
            } else {
                $skipped++;
            }
        }

        return compact('sent', 'skipped', 'total');
    }
}
