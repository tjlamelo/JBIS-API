<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Candidacy\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RecordStepPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_type' => ['sometimes', Rule::in(['FULL', 'PARTIAL', 'REFUND'])],
            'status' => ['sometimes', Rule::in(['PENDING', 'COMPLETED', 'FAILED', 'REFUNDED'])],
            'reference' => ['nullable', 'string', 'max:128'],
            'payment_method' => ['sometimes', Rule::in(['CARD', 'BANK_TRANSFER', 'CASH', 'OTHER'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
