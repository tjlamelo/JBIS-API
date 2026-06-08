<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Enums\NewsletterScope;
use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserSetting;
use Illuminate\Support\Str;

final class SubscribeNewsletterAction
{
    /**
     * @param  array{email: string, name?: string|null, language?: string, scope?: string, source?: string|null}  $data
     */
    public function execute(array $data, ?User $user = null): NewsletterSubscription
    {
        $email = strtolower(trim((string) $data['email']));
        $language = in_array(($data['language'] ?? 'fr'), ['fr', 'en'], true) ? $data['language'] : 'fr';
        $scope = NewsletterScope::tryFrom((string) ($data['scope'] ?? 'both')) ?? NewsletterScope::Both;

        $subscription = NewsletterSubscription::query()->firstOrNew(['email' => $email]);

        $subscription->fill([
            'user_id' => $user?->id ?? $subscription->user_id,
            'name' => $data['name'] ?? $user?->name ?? $subscription->name,
            'language' => $language,
            'scope' => $scope,
            'status' => NewsletterSubscriptionStatus::Subscribed,
            'source' => $data['source'] ?? $subscription->source,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);

        if (! $subscription->unsubscribe_token) {
            $subscription->unsubscribe_token = Str::random(48);
        }

        $subscription->save();

        if ($user !== null) {
            $user->loadMissing('settings');
            $this->syncUserMarketing($user, true, $scope->value, $language);
        }

        return $subscription->fresh();
    }

    private function syncUserMarketing(User $user, bool $newsletter, string $scope, string $language): void
    {
        $settings = $user->settings;
        if ($settings === null) {
            return;
        }

        $marketing = $settings->marketing ?? UserSetting::defaultMarketing();
        $marketing['newsletter'] = $newsletter;
        $marketing['newsletter_scope'] = $scope;
        $settings->marketing = $marketing;
        $settings->language = $language;
        $settings->save();
    }
}
