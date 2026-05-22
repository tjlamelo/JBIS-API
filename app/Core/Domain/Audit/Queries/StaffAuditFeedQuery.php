<?php

declare(strict_types=1);

namespace App\Core\Domain\Audit\Queries;

use App\Core\Domain\Audit\Models\Audit;
use App\Core\Domain\Audit\Support\AuditCandidateResolver;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Carbon;

final class StaffAuditFeedQuery
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forActor(int $actorUserId, int $limit = 40): array
    {
        return Audit::query()
            ->where('user_id', $actorUserId)
            ->with([
                'actor:id,first_name,last_name',
                'auditable' => static function ($query): void {
                    if (method_exists($query, 'withTrashed')) {
                        $query->withTrashed();
                    }
                },
            ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Audit $audit): array => $this->mapAudit($audit))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function globalRecent(int $limit = 30): array
    {
        return Audit::query()
            ->with([
                'actor:id,first_name,last_name',
                'auditable' => static function ($query): void {
                    if (method_exists($query, 'withTrashed')) {
                        $query->withTrashed();
                    }
                },
            ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Audit $audit): array => $this->mapAudit($audit))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAudit(Audit $audit): array
    {
        $auditable = $audit->auditable;
        $candidate = AuditCandidateResolver::fromAuditable($auditable);

        return [
            'id' => 'audit-'.$audit->id,
            'source' => 'audit',
            'action' => 'audit.'.$audit->event,
            'meta' => [
                'resource' => AuditCandidateResolver::resourceLabel($auditable),
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
            ],
            'created_at' => $audit->created_at instanceof Carbon
                ? $audit->created_at->toIso8601String()
                : $audit->created_at,
            'application' => null,
            'candidate' => $candidate,
            'step' => null,
            'actor' => $audit->actor ? [
                'id' => $audit->actor->id,
                'first_name' => $audit->actor->first_name,
                'last_name' => $audit->actor->last_name,
            ] : null,
        ];
    }
}
