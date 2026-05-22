<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Controllers;

use App\Core\Domain\Candidacy\Models\Appointment;
use App\Core\Domain\Communication\Models\DiscoverySource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentPublicController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'agency_id' => ['required', 'integer', 'exists:agencies,id'],
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', 'in:IN_PERSON,ONLINE,PHONE'],
            'discovery_source_id' => ['nullable', 'integer', 'exists:discovery_sources,id'],
            'discovery_source_other' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:128'],
            'utm_medium' => ['nullable', 'string', 'max:128'],
            'utm_campaign' => ['nullable', 'string', 'max:128'],
        ]);

        if (($data['discovery_source_id'] ?? null) !== null) {
            $source = DiscoverySource::query()->find($data['discovery_source_id']);
            if ($source && $source->key !== 'other') {
                $data['discovery_source_other'] = null;
            }
        }

        $appointment = Appointment::query()->create([
            ...$data,
            'duration_minutes' => (int) ($data['duration_minutes'] ?? 30),
            'status' => 'PENDING',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'id' => $appointment->id,
            'message' => 'ok',
        ], 201);
    }
}
