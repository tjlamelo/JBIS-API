<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Identity\Models\UserSetting;

final class UnsubscribeNewsletterAction
{
    public function executeByToken(string $token): ?NewsletterSubscription
    {
        $subscription = NewsletterSubscription::query()
            ->where('unsubscribe_token', $token)
            ->first();

        if ($subscription === null) {
            return null;
        }

        return $this->unsubscribe($subscription);
    }

    public function executeByEmail(string $email): ?NewsletterSubscription
    {
        $subscription = NewsletterSubscription::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        if ($subscription === null) {
            return null;
        }

        return $this->unsubscribe($subscription);
    }

    private function unsubscribe(NewsletterSubscription $subscription): NewsletterSubscription
    {
        $subscription->status = NewsletterSubscriptionStatus::Unsubscribed;
        $subscription->unsubscribed_at = now();
        $subscription->save();

        if ($subscription->user_id !== null) {
            $user = $subscription->user()->with('settings')->first();
            if ($user?->settings !== null) {
                $marketing = $user->settings->marketing ?? UserSetting::defaultMarketing();
                $marketing['newsletter'] = false;
                $user->settings->marketing = $marketing;
                $user->settings->save();
            }
        }

        return $subscription->fresh();
    }
}
