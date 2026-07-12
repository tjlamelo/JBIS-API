<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Application\Mail\Jobs\SendOfferNewsletterMailJob;
use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Communication\Services\OfferNewsletterContentBuilder;
use App\Core\Domain\Identity\Support\UserPersonName;
use Illuminate\Support\Carbon;

final class DispatchOfferNewslettersAction
{
    private const CHUNK = 100;

    public function __construct(
        private readonly OfferNewsletterContentBuilder $contentBuilder,
    ) {}

    /**
     * @return array{queued: int, skipped: int, total: int, batch: string}
     */
    public function execute(?int $limit = null, bool $force = false): array
    {
        $tz = 'Africa/Douala';
        $now = Carbon::now($tz);
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $batchKey = $weekStart->toDateString();

        $queued = 0;
        $skipped = 0;
        $total = 0;
        $contentCache = [];

        $query = NewsletterSubscription::query()
            ->where('status', NewsletterSubscriptionStatus::Subscribed)
            ->with(['user.profile'])
            ->orderBy('id');

        $query->chunkById(self::CHUNK, function ($subscriptions) use (
            $limit,
            $force,
            $weekStart,
            $batchKey,
            &$queued,
            &$skipped,
            &$total,
            &$contentCache,
        ): bool {
            foreach ($subscriptions as $subscription) {
                if ($limit !== null && ($queued + $skipped) >= $limit) {
                    return false;
                }

                $total++;

                if (! $force
                    && $subscription->last_sent_at !== null
                    && $subscription->last_sent_at->greaterThanOrEqualTo($weekStart)
                ) {
                    $skipped++;

                    continue;
                }

                $locale = in_array($subscription->language, ['fr', 'en'], true) ? $subscription->language : 'fr';
                $scopeValue = $subscription->scope instanceof \BackedEnum
                    ? $subscription->scope->value
                    : (string) $subscription->scope;
                $cacheKey = $scopeValue.'|'.$locale;

                if (! isset($contentCache[$cacheKey])) {
                    $contentCache[$cacheKey] = $this->contentBuilder->build($subscription->scope, $locale);
                }

                $content = $contentCache[$cacheKey];
                if (! $content['has_national'] && ! $content['has_international']) {
                    $skipped++;

                    continue;
                }

                // Personnalisation du prénom si abonné lié à un compte.
                if ((! $subscription->name || trim((string) $subscription->name) === '') && $subscription->user) {
                    $contact = UserPersonName::toContactArray($subscription->user);
                    $first = trim((string) ($contact['first_name'] ?? ''));
                    if ($first !== '') {
                        $subscription->setAttribute('name', $first);
                    }
                }

                SendOfferNewsletterMailJob::dispatch(
                    $subscription->id,
                    $content,
                    $locale,
                    $batchKey,
                );

                $queued++;
            }

            return true;
        });

        return [
            'queued' => $queued,
            'skipped' => $skipped,
            'total' => $total,
            'batch' => $batchKey,
        ];
    }
}
