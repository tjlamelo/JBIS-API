<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Domain\Candidacy\Models\Appointment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $status = $request->query('status');
        $from = $request->query('from');
        $to = $request->query('to');
        $search = $request->query('search');

        $userId = $request->query('user_id');

        $items = Appointment::query()
            ->with([
                'agency:id,name,address',
                'discoverySource:id,key,label',
            ])
            ->when($userId, fn ($q) => $q->where('user_id', (int) $userId))
            ->when($search, function ($q) use ($search) {
                $value = (string) $search;
                $q->where(function ($q) use ($value) {
                    $q->where('full_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                });
            })
            ->when($status, fn ($q) => $q->where('status', (string) $status))
            ->when($from && $to, fn ($q) => $q->whereBetween('scheduled_at', [(string) $from, (string) $to]))
            ->orderByDesc('scheduled_at')
            ->select([
                'id',
                'agency_id',
                'full_name',
                'email',
                'phone',
                'scheduled_at',
                'type',
                'status',
                'subject',
                'message',
                'discovery_source_id',
                'discovery_source_other',
                'created_at',
            ])
            ->paginate($perPage);

        return response()->json($items);
    }
}
