<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Resources\UserSecurityEventResource;
use App\Core\Domain\Identity\Models\UserSecurityEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserSecurityEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserSecurityEvent::class);

        $items = UserSecurityEvent::query()
            ->with(['user:id,name,email', 'device'])
            ->when($request->query('user_id'), fn ($q, $id) => $q->where('user_id', (int) $id))
            ->when($request->query('event'), fn ($q, $e) => $q->where('event', $e))
            ->when($request->query('risk_level'), fn ($q, $l) => $q->where('risk_level', $l))
            ->when($request->query('from'), fn ($q, $d) => $q->where('occurred_at', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->where('occurred_at', '<=', $d))
            ->when($request->query('min_score'), fn ($q, $s) => $q->where('risk_score', '>=', (int) $s))
            ->when($request->query('q'), function ($q) use ($request): void {
                $term = '%'.trim((string) $request->query('q')).'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('ip', 'like', $term)
                        ->orWhere('user_agent', 'like', $term)
                        ->orWhere('event', 'like', $term)
                        ->orWhereHas('user', function ($u) use ($term): void {
                            $u->where('name', 'like', $term)->orWhere('email', 'like', $term);
                        });
                });
            })
            ->orderByDesc('occurred_at')
            ->paginate(max(1, min(100, (int) $request->integer('per_page', 25))));

        $levels = UserSecurityEvent::query()
            ->select('risk_level')
            ->distinct()
            ->orderBy('risk_level')
            ->pluck('risk_level');

        $events = UserSecurityEvent::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return BaseResponse::ok([
            'security_events' => UserSecurityEventResource::collection($items->items()),
            'filters' => [
                'risk_levels' => $levels,
                'events' => $events,
            ],
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function show(UserSecurityEvent $userSecurityEvent): JsonResponse
    {
        $this->authorize('view', $userSecurityEvent);
        $userSecurityEvent->load(['user:id,name,email', 'device']);

        return BaseResponse::ok([
            'security_event' => new UserSecurityEventResource($userSecurityEvent),
        ])->toJsonResponse();
    }
}
