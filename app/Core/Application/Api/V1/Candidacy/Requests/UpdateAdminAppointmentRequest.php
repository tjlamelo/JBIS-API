<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAdminAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');

        return $appointment !== null && $this->user()?->can('update', $appointment) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['PENDING', 'CONFIRMED', 'CANCELLED', 'COMPLETED', 'NOSHOW'])],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'scheduled_at' => ['sometimes', 'date'],
            'type' => ['sometimes', 'string', Rule::in(['IN_PERSON', 'ONLINE', 'PHONE'])],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
