<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Controllers;

use App\Core\Domain\Candidacy\Enums\ContactReason;
use App\Core\Domain\Candidacy\Models\Appointment;
use App\Core\Domain\Communication\Models\DiscoverySource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AppointmentPublicController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_reason' => ['required', 'string', Rule::in(ContactReason::values())],
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'scheduled_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['nullable', 'in:IN_PERSON,ONLINE,PHONE'],
            'discovery_source_id' => ['nullable', 'integer', 'exists:discovery_sources,id'],
            'discovery_source_other' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:128'],
            'utm_medium' => ['nullable', 'string', 'max:128'],
            'utm_campaign' => ['nullable', 'string', 'max:128'],
        ]);

        $validator = validator($data);
        $validator->after(function (Validator $v) use ($data): void {
            $email = trim((string) ($data['email'] ?? ''));
            $phone = trim((string) ($data['phone'] ?? ''));
            if ($email === '' && $phone === '') {
                $v->errors()->add('email', __('Indiquez au moins un email ou un numéro de téléphone.'));
            }

            $reason = ContactReason::from((string) $data['contact_reason']);
            if ($reason === ContactReason::Appointment && empty($data['scheduled_at'])) {
                $v->errors()->add('scheduled_at', __('Indiquez une date pour votre rendez-vous.'));
            }
        });

        $validator->validate();

        if (($data['discovery_source_id'] ?? null) !== null) {
            $source = DiscoverySource::query()->find($data['discovery_source_id']);
            if ($source && $source->key !== 'other') {
                $data['discovery_source_other'] = null;
            }
        }

        $reason = ContactReason::from((string) $data['contact_reason']);
        $type = $data['type'] ?? ($reason === ContactReason::Appointment ? 'IN_PERSON' : null);

        $appointment = Appointment::query()->create([
            ...$data,
            'type' => $type,
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
