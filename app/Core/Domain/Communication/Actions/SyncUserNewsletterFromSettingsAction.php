<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Enums\NewsletterScope;
use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserSetting;
use Illuminate\Support\Str;

final class SyncUserNewsletterFromSettingsAction
{
    public function execute(User $user, UserSetting $settings): void
    {
        $marketing = $settings->marketing ?? UserSetting::defaultMarketing();
        $enabled = (bool) ($marketing['newsletter'] ?? false);
        $scope = NewsletterScope::tryFrom((string) ($marketing['newsletter_scope'] ?? 'both')) ?? NewsletterScope::Both;
        $email = strtolower(trim((string) $user->email));

        if ($email === '') {
            return;
        }

        $subscription = NewsletterSubscription::query()->firstOrNew(['email' => $email]);
        $subscription->user_id = $user->id;
        $subscription->name = $user->name;
        $subscription->language = in_array($settings->language, ['fr', 'en'], true) ? $settings->language : 'fr';
        $subscription->scope = $scope;

        if (! $subscription->unsubscribe_token) {
            $subscription->unsubscribe_token = Str::random(48);
        }

        if ($enabled) {
            $subscription->status = NewsletterSubscriptionStatus::Subscribed;
            $subscription->subscribed_at = $subscription->subscribed_at ?? now();
            $subscription->unsubscribed_at = null;
            $subscription->source = $subscription->source ?? 'settings';
        } else {
            $subscription->status = NewsletterSubscriptionStatus::Unsubscribed;
            $subscription->unsubscribed_at = now();
        }

        $subscription->save();
    }
}
