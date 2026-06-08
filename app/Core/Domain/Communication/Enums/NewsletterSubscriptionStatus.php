<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Enums;

enum NewsletterSubscriptionStatus: string
{
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
}
