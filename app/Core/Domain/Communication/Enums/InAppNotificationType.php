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
    case OpsMeetingInvite = 'ops_meeting_invite';
    case OpsMeetingPresent = 'ops_meeting_present';
    case OpsTaskAssigned = 'ops_task_assigned';
    case OpsTaskCompleted = 'ops_task_completed';
    case OpsTaskOverdue = 'ops_task_overdue';
    case OpsTaskNotSubmitted = 'ops_task_not_submitted';
    case OpsWeeklyPersonal = 'ops_weekly_personal';
    case OpsWeeklyManagement = 'ops_weekly_management';
}
