<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Application\Mail\Jobs\SendOfferNewsletterMailJob;
use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Communication\Services\OfferNewsletterContentBuilder;
use App\Core\Domain\Identity\Support\UserPersonName;
use Illuminate\Support\Carbon;

final class SendOfferNewsletterAction
{
    public function __construct(
        private readonly OfferNewsletterContentBuilder $contentBuilder,
    ) {}

    public function execute(NewsletterSubscription $subscription, bool $force = false): bool
    {
        if ($subscription->status !== NewsletterSubscriptionStatus::Subscribed) {
            return false;
        }

        $tz = 'Africa/Douala';
        $weekStart = Carbon::now($tz)->startOfWeek(Carbon::MONDAY)->startOfDay();
        $batchKey = $weekStart->toDateString();

        if (! $force
            && $subscription->last_sent_at !== null
            && $subscription->last_sent_at->greaterThanOrEqualTo($weekStart)
        ) {
            return false;
        }

        $locale = in_array($subscription->language, ['fr', 'en'], true) ? $subscription->language : 'fr';
        $content = $this->contentBuilder->build($subscription->scope, $locale);

        if (! $content['has_national'] && ! $content['has_international']) {
            return false;
        }

        $subscription->loadMissing('user.profile');
        if ((! $subscription->name || trim((string) $subscription->name) === '') && $subscription->user) {
            $contact = UserPersonName::toContactArray($subscription->user);
            $first = trim((string) ($contact['first_name'] ?? ''));
            if ($first !== '') {
                $subscription->name = $first;
                $subscription->save();
            }
        }

        SendOfferNewsletterMailJob::dispatch(
            $subscription->id,
            $content,
            $locale,
            $force ? $batchKey.'-force-'.now()->format('His') : $batchKey,
        );

        return true;
    }
}
