<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Queries;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStepEvent;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Trade;
use App\Core\Domain\Communication\Models\DiscoverySource;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Séries et répartitions pour les graphiques dashboard.
 */
final class DashboardChartsQuery
{
    /**
     * @return array{
     *   applications_by_day: list<array{date: string, label: string, count: int}>,
     *   applications_by_status: list<array{key: string, label: string, value: int}>,
     *   users_by_role: list<array{key: string, label: string, value: int}>,
     *   offers_by_month: list<array{month: string, label: string, count: int}>,
     *   opened_comparison: list<array{key: string, label: string, value: int}>,
     *   discovery_sources: list<array{key: string, label: string, value: int, share: float}>,
     *   profiles_by_trade: list<array{key: string, label: string, value: int}>
     * }
     */
    public function forAdmin(): array
    {
        $now = Carbon::now();

        return [
            'applications_by_day' => $this->applicationsLastDays(14),
            'applications_by_status' => $this->applicationsByStatus(),
            'users_by_role' => $this->usersByRole(),
            'offers_by_month' => $this->offersLastMonths(6),
            'opened_comparison' => [
                [
                    'key' => 'week',
                    'label' => 'Cette semaine',
                    'value' => $this->applicationsCreatedSince($now->copy()->startOfWeek()),
                ],
                [
                    'key' => 'month',
                    'label' => 'Ce mois',
                    'value' => $this->applicationsCreatedSince($now->copy()->startOfMonth()),
                ],
                [
                    'key' => 'year',
                    'label' => 'Cette année',
                    'value' => $this->applicationsCreatedSince($now->copy()->startOfYear()),
                ],
            ],
            'discovery_sources' => $this->discoverySourcesDistribution(),
            'profiles_by_trade' => $this->profilesByTrade(15),
        ];
    }

    /**
     * @return array{
     *   my_actions_by_day: list<array{date: string, label: string, count: int}>,
     *   applications_by_status: list<array{key: string, label: string, value: int}>,
     *   workload: list<array{key: string, label: string, value: int}>
     * }
     */
    public function forStaff(int $actorUserId): array
    {
        return [
            'my_actions_by_day' => $this->actorActionsLastDays($actorUserId, 14),
            'applications_by_status' => $this->applicationsByStatus(),
            'workload' => [
                [
                    'key' => 'pending',
                    'label' => 'En attente',
                    'value' => Application::query()
                        ->where('status', ApplicationStatus::Pending->value)
                        ->count(),
                ],
                [
                    'key' => 'in_progress',
                    'label' => 'En cours (mes dossiers)',
                    'value' => Application::query()
                        ->where('status', ApplicationStatus::InProgress->value)
                        ->whereHas('events', function ($query) use ($actorUserId): void {
                            $query->where('actor_user_id', $actorUserId);
                        })
                        ->count(),
                ],
                [
                    'key' => 'today',
                    'label' => "Actions aujourd'hui",
                    'value' => ApplicationStepEvent::query()
                        ->where('actor_user_id', $actorUserId)
                        ->where('created_at', '>=', Carbon::now()->startOfDay())
                        ->count(),
                ],
            ],
        ];
    }

    /**
     * @return array{
     *   offers_by_status: list<array{key: string, label: string, value: int}>,
     *   assignments_by_month: list<array{month: string, label: string, count: int}>
     * }
     */
    public function forRecruiter(int $organizationId): array
    {
        $statusRows = RecruiterOfferSubmission::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->where('recruiter_organization_id', $organizationId)
            ->groupBy('status')
            ->get();

        $statusLabels = [
            'draft' => 'Brouillon',
            'submitted' => 'Soumise',
            'in_review' => 'En revue',
            'needs_changes' => 'À corriger',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
        ];

        $offersByStatus = $statusRows->map(static function ($row) use ($statusLabels): array {
            $key = $row->status instanceof \BackedEnum ? $row->status->value : (string) $row->status;

            return [
                'key' => $key,
                'label' => $statusLabels[$key] ?? $key,
                'value' => (int) $row->aggregate,
            ];
        })->values()->all();

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $count = RecruiterProfileAssignment::query()
                ->where('recruiter_organization_id', $organizationId)
                ->whereBetween('assigned_at', [$start, $end])
                ->count();

            $months[] = [
                'month' => $start->format('Y-m'),
                'label' => $start->translatedFormat('M Y'),
                'count' => $count,
            ];
        }

        return [
            'offers_by_status' => $offersByStatus,
            'assignments_by_month' => $months,
        ];
    }

    /**
     * @return list<array{date: string, label: string, count: int}>
     */
    private function applicationsLastDays(int $days): array
    {
        $start = Carbon::now()->subDays($days - 1)->startOfDay();
        $rows = Application::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as aggregate'))
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->startOfDay();
            $key = $day->toDateString();
            $series[] = [
                'date' => $key,
                'label' => $day->format('d/m'),
                'count' => (int) ($rows[$key] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * @return list<array{key: string, label: string, value: int}>
     */
    private function applicationsByStatus(): array
    {
        $rows = Application::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $out = [];
        foreach (ApplicationStatus::cases() as $status) {
            $out[] = [
                'key' => $status->value,
                'label' => $status->label('fr'),
                'value' => (int) ($rows[$status->value] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{key: string, label: string, value: int}>
     */
    private function usersByRole(): array
    {
        $labels = [
            ApplicationRole::CANDIDATE => 'Candidats',
            ApplicationRole::STAFF => 'Staff',
            ApplicationRole::ADMIN => 'Admins',
            ApplicationRole::RECRUITER => 'Recruteurs',
            ApplicationRole::PARTNER => 'Partenaires',
        ];

        $out = [];
        foreach ($labels as $role => $label) {
            $out[] = [
                'key' => $role,
                'label' => $label,
                'value' => User::role($role)->count(),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{month: string, label: string, count: int}>
     */
    private function offersLastMonths(int $months): array
    {
        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = Carbon::now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $series[] = [
                'month' => $start->format('Y-m'),
                'label' => $start->translatedFormat('M Y'),
                'count' => Offer::query()
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ];
        }

        return $series;
    }

    /**
     * @return list<array{date: string, label: string, count: int}>
     */
    private function actorActionsLastDays(int $actorUserId, int $days): array
    {
        $start = Carbon::now()->subDays($days - 1)->startOfDay();
        $rows = ApplicationStepEvent::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as aggregate'))
            ->where('actor_user_id', $actorUserId)
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->startOfDay();
            $key = $day->toDateString();
            $series[] = [
                'date' => $key,
                'label' => $day->format('d/m'),
                'count' => (int) ($rows[$key] ?? 0),
            ];
        }

        return $series;
    }

    private function applicationsCreatedSince(Carbon $since): int
    {
        return Application::query()
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Répartition des profils candidats par canal de découverte.
     *
     * @return list<array{key: string, label: string, value: int, share: float}>
     */
    private function discoverySourcesDistribution(): array
    {
        $rows = UserProfile::query()
            ->select('discovery_source_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('discovery_source_id')
            ->get();

        $bySourceId = [];
        $unspecified = 0;
        foreach ($rows as $row) {
            if ($row->discovery_source_id === null) {
                $unspecified = (int) $row->aggregate;
                continue;
            }
            $bySourceId[(int) $row->discovery_source_id] = (int) $row->aggregate;
        }

        $sources = DiscoverySource::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'key', 'label']);

        $out = [];
        foreach ($sources as $source) {
            $value = $bySourceId[(int) $source->id] ?? 0;
            if ($value === 0) {
                continue;
            }
            $out[] = [
                'key' => (string) $source->key,
                'label' => (string) $source->label,
                'value' => $value,
                'share' => 0.0,
            ];
        }

        if ($unspecified > 0) {
            $out[] = [
                'key' => 'unspecified',
                'label' => 'Non renseigné',
                'value' => $unspecified,
                'share' => 0.0,
            ];
        }

        $grandTotal = array_sum(array_column($out, 'value'));
        foreach ($out as &$item) {
            $item['share'] = $grandTotal > 0
                ? round(($item['value'] / $grandTotal) * 100, 1)
                : 0.0;
        }
        unset($item);

        usort($out, static fn (array $a, array $b): int => $b['value'] <=> $a['value']);

        return $out;
    }

    /**
     * Métiers les plus représentés parmi les profils (pivot user_trade).
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    private function profilesByTrade(int $limit = 15): array
    {
        $rows = DB::table('user_trade')
            ->select('trade_id', DB::raw('COUNT(DISTINCT user_id) as aggregate'))
            ->groupBy('trade_id')
            ->orderByDesc('aggregate')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $trades = Trade::query()
            ->whereIn('id', $rows->pluck('trade_id')->all())
            ->get(['id', 'name', 'slug'])
            ->keyBy('id');

        return $rows->map(static function ($row) use ($trades): array {
            $trade = $trades->get((int) $row->trade_id);
            $label = $trade
                ? ((string) $trade->getTranslation('name', 'fr', false)
                    ?: (string) $trade->getTranslation('name', 'en', false)
                    ?: (string) ($trade->slug ?? "Métier #{$trade->id}"))
                : "Métier #{$row->trade_id}";

            return [
                'key' => (string) $row->trade_id,
                'label' => $label,
                'value' => (int) $row->aggregate,
            ];
        })->values()->all();
    }
}
