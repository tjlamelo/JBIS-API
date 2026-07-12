<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Enums;

enum InAppNotificationType: string
{
    case WeekStart = 'week_start';
    case Weekend = 'weekend';
    case Holiday = 'holiday';
    case Birthday = 'birthday';
    case BirthdayFollowUp = 'birthday_followup';
    case OfferRecommendation = 'offer_recommendation';
    case System = 'system';
}
