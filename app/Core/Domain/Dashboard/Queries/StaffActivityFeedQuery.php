<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Queries;

use App\Core\Domain\Audit\Queries\StaffAuditFeedQuery;
use App\Core\Domain\Candidacy\Models\ApplicationStepEvent;
use App\Core\Domain\Identity\Support\UserPersonName;
use Illuminate\Support\Carbon;

final class StaffActivityFeedQuery
{
    public function __construct(
        private readonly StaffAuditFeedQuery $auditFeed,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function forActor(int $actorUserId, int $limit = 40, string $locale = 'fr'): array
    {
        return $this->mergeFeeds(
            $this->mapApplicationEvents(
                ApplicationStepEvent::query()
                    ->where('actor_user_id', $actorUserId)
                    ->with([
                        'application:id,application_number,user_id',
                        ...UserPersonName::withProfile('application.user'),
                        'applicationStep:id,title,step_type',
                        ...UserPersonName::withProfile('actor'),
                    ])
                    ->orderByDesc('created_at')
                    ->limit($limit)
                    ->get(),
                $locale,
            ),
            $this->auditFeed->forActor($actorUserId, $limit),
            $limit,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function globalRecent(int $limit = 30, string $locale = 'fr'): array
    {
        return $this->mergeFeeds(
            $this->mapApplicationEvents(
                ApplicationStepEvent::query()
                    ->with([
                        'application:id,application_number,user_id',
                        ...UserPersonName::withProfile('application.user'),
                        'applicationStep:id,title,step_type',
                        ...UserPersonName::withProfile('actor'),
                    ])
                    ->orderByDesc('created_at')
                    ->limit($limit)
                    ->get(),
                $locale,
            ),
            $this->auditFeed->globalRecent($limit),
            $limit,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $audits
     * @return list<array<string, mixed>>
     */
    private function mergeFeeds(array $events, array $audits, int $limit): array
    {
        $items = [...$events, ...$audits];

        usort($items, static function (array $a, array $b): int {
            $ta = isset($a['created_at']) ? strtotime((string) $a['created_at']) : 0;
            $tb = isset($b['created_at']) ? strtotime((string) $b['created_at']) : 0;

            return $tb <=> $ta;
        });

        return array_slice($items, 0, $limit);
    }

    /**
     * @param  iterable<int, ApplicationStepEvent>  $events
     * @return list<array<string, mixed>>
     */
    private function mapApplicationEvents(iterable $events, string $locale): array
    {
        $mapped = [];
        foreach ($events as $event) {
            $mapped[] = $this->mapEvent($event, $locale);
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEvent(ApplicationStepEvent $event, string $locale): array
    {
        $app = $event->application;
        $user = $app?->user;
        $step = $event->applicationStep;
        $title = $step?->title;
        if (is_array($title)) {
            $title = $title[$locale] ?? $title['fr'] ?? $title['en'] ?? null;
        }

        return [
            'id' => 'event-'.$event->id,
            'source' => 'application',
            'action' => $event->action,
            'meta' => $event->meta,
            'created_at' => $event->created_at instanceof Carbon
                ? $event->created_at->toIso8601String()
                : $event->created_at,
            'application' => $app ? [
                'id' => $app->id,
                'application_number' => $app->application_number,
            ] : null,
            'candidate' => $user ? UserPersonName::toContactArray($user) : null,
            'step' => $step ? [
                'id' => $step->id,
                'title' => $title,
                'step_type' => $step->step_type instanceof \BackedEnum
                    ? $step->step_type->value
                    : (string) $step->step_type,
            ] : null,
            'actor' => $event->actor ? UserPersonName::toActorArray($event->actor) : null,
        ];
    }
}
