<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Queries;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserVisaHistory;
use App\Core\Domain\Identity\States\VisaHistoryStatus;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use Illuminate\Support\Carbon;

final class AdminDashboardStatsQuery
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $now = Carbon::now();

        return [
            'applications_opened' => [
                'week' => $this->applicationsCreatedSince($now->copy()->startOfWeek()),
                'month' => $this->applicationsCreatedSince($now->copy()->startOfMonth()),
                'year' => $this->applicationsCreatedSince($now->copy()->startOfYear()),
            ],
            'visas_granted' => UserVisaHistory::query()
                ->where('status', VisaHistoryStatus::Granted->value)
                ->count(),
            'candidates_total' => User::role(ApplicationRole::CANDIDATE)->count(),
            'staff_total' => User::role([
                ApplicationRole::STAFF,
                ApplicationRole::ADMIN,
                ApplicationRole::SUPERADMIN,
            ])->count(),
            'applications_in_progress' => Application::query()
                ->where('status', ApplicationStatus::InProgress->value)
                ->count(),
            'applications_pending' => Application::query()
                ->where('status', ApplicationStatus::Pending->value)
                ->count(),
            'procedures_published' => ProcessFlow::query()
                ->where('status', ProcessFlowStatus::Published->value)
                ->count(),
            'applications_total' => Application::query()->count(),
        ];
    }

    private function applicationsCreatedSince(Carbon $since): int
    {
        return Application::query()
            ->where('created_at', '>=', $since)
            ->count();
    }
}
