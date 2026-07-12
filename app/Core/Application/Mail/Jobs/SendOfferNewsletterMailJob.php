<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Jobs;

use App\Core\Application\Mail\Mailable\OfferNewsletterMail;
use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendOfferNewsletterMailJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 4;

    public int $timeout = 90;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public int $uniqueFor = 86000;

    /**
     * @param  array{national: list<array<string, mixed>>, international: list<array<string, mixed>>, has_national: bool, has_international: bool}  $content
     */
    public function __construct(
        public readonly int $subscriptionId,
        public readonly array $content,
        public readonly string $language,
        public readonly string $batchKey,
    ) {
        $this->onQueue((string) config('queue.mail_queue', 'mail'));
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return 'newsletter-offer:'.$this->subscriptionId.':'.$this->batchKey;
    }

    public function handle(): void
    {
        $subscription = NewsletterSubscription::query()->with('user.profile')->find($this->subscriptionId);
        if ($subscription === null || $subscription->status !== NewsletterSubscriptionStatus::Subscribed) {
            return;
        }

        if (! $this->content['has_national'] && ! $this->content['has_international']) {
            return;
        }

        if ((! $subscription->name || trim((string) $subscription->name) === '') && $subscription->user) {
            $contact = \App\Core\Domain\Identity\Support\UserPersonName::toContactArray($subscription->user);
            $first = trim((string) ($contact['first_name'] ?? ''));
            if ($first !== '') {
                $subscription->name = $first;
            }
        }

        Mail::to($subscription->email)->send(
            new OfferNewsletterMail($subscription, $this->content, $this->language)
        );

        $subscription->forceFill(['last_sent_at' => now()])->save();

        Log::info('newsletter_offer_sent', [
            'subscription_id' => $subscription->id,
            'email' => $subscription->email,
            'batch' => $this->batchKey,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('newsletter_offer_failed', [
            'subscription_id' => $this->subscriptionId,
            'batch' => $this->batchKey,
            'error' => $exception->getMessage(),
        ]);
    }
}
