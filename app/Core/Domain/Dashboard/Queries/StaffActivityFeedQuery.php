<?php

declare(strict_types=1);

namespace App\Core\Domain\Dashboard\Queries;

use App\Core\Domain\Audit\Queries\StaffAuditFeedQuery;
use App\Core\Domain\Candidacy\Models\ApplicationStepEvent;
use Illuminate\Support\Carbon;

final class StaffActivityFeedQuery
{
    public function __construct(
        private readonly StaffAuditFeedQuery $auditFeed,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function forActor(int $actorUserId, int $limit = 40): array
    {
        return $this->mergeFeeds(
            $this->mapApplicationEvents(
                ApplicationStepEvent::query()
                    ->where('actor_user_id', $actorUserId)
                    ->with([
                        'application:id,application_number,user_id',
                        'application.user:id,first_name,last_name,email',
                        'applicationStep:id,title,step_type',
                        'actor:id,first_name,last_name',
                    ])
                    ->orderByDesc('created_at')
                    ->limit($limit)
                    ->get(),
            ),
            $this->auditFeed->forActor($actorUserId, $limit),
            $limit,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function globalRecent(int $limit = 30): array
    {
        return $this->mergeFeeds(
            $this->mapApplicationEvents(
                ApplicationStepEvent::query()
                    ->with([
                        'application:id,application_number,user_id',
                        'application.user:id,first_name,last_name,email',
                        'applicationStep:id,title,step_type',
                        'actor:id,first_name,last_name',
                    ])
                    ->orderByDesc('created_at')
                    ->limit($limit)
                    ->get(),
            ),
            $this->auditFeed->globalRecent($limit),
            $limit,
        );
    }

    /**
     * @param  iterable<int, ApplicationStepEvent>  $events
     * @param  list<array<string, mixed>>  $audits
     * @return list<array<string, mixed>>
     */
    private function mergeFeeds(iterable $events, array $audits, int $limit): array
    {
        $items = [...$this->mapApplicationEvents($events), ...$audits];

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
    private function mapApplicationEvents(iterable $events): array
    {
        $mapped = [];
        foreach ($events as $event) {
            $mapped[] = $this->mapEvent($event);
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEvent(ApplicationStepEvent $event): array
    {
        $app = $event->application;
        $user = $app?->user;
        $step = $event->applicationStep;
        $locale = 'fr';
        $title = $step?->title;
        if (is_array($title)) {
            $title = $title[$locale] ?? $title['en'] ?? null;
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
            'candidate' => $user ? [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ] : null,
            'step' => $step ? [
                'id' => $step->id,
                'title' => $title,
                'step_type' => $step->step_type instanceof \BackedEnum
                    ? $step->step_type->value
                    : (string) $step->step_type,
            ] : null,
            'actor' => $event->actor ? [
                'id' => $event->actor->id,
                'first_name' => $event->actor->first_name,
                'last_name' => $event->actor->last_name,
            ] : null,
        ];
    }
}
