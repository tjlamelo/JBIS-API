<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Application\Mail\Mailable\OfferNewsletterMail;
use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Communication\Services\OfferNewsletterContentBuilder;
use Illuminate\Support\Facades\Mail;

final class SendOfferNewsletterAction
{
    public function __construct(
        private readonly OfferNewsletterContentBuilder $contentBuilder,
    ) {}

    public function execute(NewsletterSubscription $subscription): bool
    {
        if ($subscription->status !== NewsletterSubscriptionStatus::Subscribed) {
            return false;
        }

        $locale = in_array($subscription->language, ['fr', 'en'], true) ? $subscription->language : 'fr';
        $content = $this->contentBuilder->build($subscription->scope, $locale);

        if (! $content['has_national'] && ! $content['has_international']) {
            return false;
        }

        Mail::to($subscription->email)->send(new OfferNewsletterMail($subscription, $content, $locale));

        $subscription->last_sent_at = now();
        $subscription->save();

        return true;
    }
}
