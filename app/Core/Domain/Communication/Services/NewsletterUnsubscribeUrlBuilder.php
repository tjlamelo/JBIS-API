<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Services;

use App\Core\Domain\Communication\Models\NewsletterSubscription;

final class NewsletterUnsubscribeUrlBuilder
{
    public function build(NewsletterSubscription $subscription): string
    {
        $base = rtrim((string) config('services.newsletter.unsubscribe_url', env('FRONTEND_URL', 'http://localhost:3000').'/newsletter/unsubscribe'), '/');

        return $base.'?token='.urlencode((string) $subscription->unsubscribe_token);
    }
}
