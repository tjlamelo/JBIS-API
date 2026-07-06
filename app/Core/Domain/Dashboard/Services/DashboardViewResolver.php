<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Services;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Queries\ApplicationProgressQuery;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Dashboard\Queries\AdminDashboardStatsQuery;
use App\Core\Domain\Dashboard\Queries\CandidateProfileCompletionQuery;
use App\Core\Domain\Dashboard\Queries\RecruiterDashboardStatsQuery;
use App\Core\Domain\Dashboard\Queries\StaffActivityFeedQuery;
use App\Core\Domain\Dashboard\Queries\StaffDashboardStatsQuery;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Identity\Models\User;
use App\Core\Infrastructure\Cache\AppCache;

final class DashboardViewResolver
{
    public function __construct(
        private readonly AdminDashboardStatsQuery $adminStats,
        private readonly StaffActivityFeedQuery $staffActivity,
        private readonly StaffDashboardStatsQuery $staffStats,
        private readonly RecruiterDashboardStatsQuery $recruiterStats,
        private readonly ApplicationProgressQuery $applicationProgress,
        private readonly CandidateProfileCompletionQuery $profileCompletion,
        private readonly AppCache $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(User $user, string $locale = 'fr'): array
    {
        return $this->cache->remember(
            $this->cache->dashboardKey((int) $user->id, $locale),
            60,
            fn () => $this->resolveFresh($user, $locale),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFresh(User $user, string $locale): array
    {
        if ($this->hasAnyRole($user, [ApplicationRole::SUPERADMIN, ApplicationRole::ADMIN])) {
            return $this->adminPayload($user, $locale);
        }

        if ($this->hasRole($user, ApplicationRole::STAFF)) {
            return $this->staffPayload($user, $locale);
        }

        if ($this->hasRole($user, ApplicationRole::RECRUITER)) {
            return $this->recruiterPayload($user);
        }

        return $this->candidatePayload($user, $locale);
    }

    /**
     * @return array<string, mixed>
     */
    private function adminPayload(User $user, string $locale): array
    {
        return [
            'variant' => 'admin',
            'stats' => $this->adminStats->execute(),
            'my_activity' => $this->staffActivity->forActor((int) $user->id, 30, $locale),
            'global_activity' => $this->hasRole($user, ApplicationRole::SUPERADMIN)
                ? $this->staffActivity->globalRecent(40, $locale)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function staffPayload(User $user, string $locale): array
    {
        return [
            'variant' => 'staff',
            'stats' => $this->staffStats->execute((int) $user->id),
            'my_activity' => $this->staffActivity->forActor((int) $user->id, 50, $locale),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recruiterPayload(User $user): array
    {
        $data = $this->recruiterStats->forUser($user);

        return [
            'variant' => 'recruiter',
            ...$data,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function candidatePayload(User $user, string $locale): array
    {
        $applications = $this->applicationProgress->listForUser((int) $user->id);
        $active = $applications->first(
            fn (Application $app) => in_array(
                $app->status instanceof ApplicationStatus
                    ? $app->status->value
                    : (string) $app->status,
                [ApplicationStatus::InProgress->value, ApplicationStatus::Pending->value],
                true,
            ),
        ) ?? $applications->first();

        $progress = null;
        if ($active !== null) {
            $view = $this->applicationProgress->forApplication($active, $locale);
            $progress = $this->sanitizeProgressForCandidate($view->toArray());
        }

        return [
            'variant' => 'candidate',
            'profile_completion' => $this->profileCompletion->forUser($user),
            'applications_summary' => $applications->map(function (Application $app) use ($locale): array {
                $pick = static function (array|string|null $field) use ($locale): ?string {
                    if ($field === null) {
                        return null;
                    }
                    if (is_string($field)) {
                        return $field;
                    }

                    return $field[$locale] ?? $field['fr'] ?? $field['en'] ?? null;
                };

                $status = $app->status instanceof \BackedEnum ? $app->status->value : $app->status;

                return [
                    'id' => $app->id,
                    'application_number' => $app->application_number,
                    'status' => $status,
                    'status_label' => ApplicationStatus::tryFrom((string) $status)?->label($locale) ?? (string) $status,
                    'offer_label' => $app->offer ? $pick($app->offer->resolvedTitleTranslations()) : null,
                    'program_label' => $app->program ? $pick($app->program->name) : null,
                    'total_due' => (float) $app->total_due,
                    'total_paid' => (float) $app->total_paid,
                    'total_remaining' => $app->totalRemaining(),
                ];
            })->values()->all(),
            'active_application' => $progress,
        ];
    }

    /**
     * @param  array<string, mixed>  $progress
     * @return array<string, mixed>
     */
    private function sanitizeProgressForCandidate(array $progress): array
    {
        unset($progress['activity_log']);

        $progress['steps'] = array_map(static function (array $step): array {
            if (isset($step['interview']) && is_array($step['interview'])) {
                unset($step['interview']['internal_notes']);
            }

            return $step;
        }, $progress['steps'] ?? []);

        return $progress;
    }

    private function hasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * @param  list<string>  $roles
     */
    private function hasAnyRole(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
