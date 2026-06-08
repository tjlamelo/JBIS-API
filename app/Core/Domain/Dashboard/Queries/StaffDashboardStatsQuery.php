<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Queries;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStepEvent;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use Illuminate\Support\Carbon;

final class StaffDashboardStatsQuery
{
    /**
     * @return array<string, int>
     */
    public function execute(int $actorUserId): array
    {
        $todayStart = Carbon::now()->startOfDay();

        $applicationsInProgress = Application::query()
            ->where('status', ApplicationStatus::InProgress->value)
            ->whereHas('events', function ($query) use ($actorUserId): void {
                $query->where('actor_user_id', $actorUserId);
            })
            ->count();

        return [
            'applications_pending' => Application::query()
                ->where('status', ApplicationStatus::Pending->value)
                ->count(),
            'applications_in_progress' => $applicationsInProgress,
            'my_actions_today' => ApplicationStepEvent::query()
                ->where('actor_user_id', $actorUserId)
                ->where('created_at', '>=', $todayStart)
                ->count(),
        ];
    }
}
