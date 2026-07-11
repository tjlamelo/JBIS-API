<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Candidacy\Requests\UpdateAdminAppointmentRequest;
use App\Core\Domain\Candidacy\Models\Appointment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Appointment::class);

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
                'user_id',
                'agency_id',
                'contact_reason',
                'full_name',
                'email',
                'phone',
                'scheduled_at',
                'type',
                'status',
                'subject',
                'message',
                'internal_notes',
                'discovery_source_id',
                'discovery_source_other',
                'created_at',
            ])
            ->paginate($perPage);

        return response()->json($items);
    }

    public function update(UpdateAdminAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $appointment->fill($request->validated());
        $appointment->save();

        $appointment->load([
            'agency:id,name,address',
            'discoverySource:id,key,label',
        ]);

        return BaseResponse::ok([
            'message' => __('Rendez-vous mis a jour.'),
            'appointment' => $appointment,
        ])->toJsonResponse();
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return BaseResponse::ok([
            'message' => __('Rendez-vous supprime.'),
        ])->toJsonResponse();
    }
}
