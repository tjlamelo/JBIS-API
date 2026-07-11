<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\CertificationOffer;

use App\Core\Domain\Catalog\DTOs\CertificationOffer\CertificationOfferDto;
use App\Core\Domain\Catalog\Models\CertificationOffer;
use App\Core\Domain\Catalog\States\CertificationExamMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCertificationOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offer = $this->route('certificationOffer');

        return $offer instanceof CertificationOffer
            && ($this->user()?->can('update', $offer) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $modes = array_map(static fn (CertificationExamMode $m) => $m->value, CertificationExamMode::cases());

        return [
            'domain' => ['sometimes', 'string', 'max:120'],
            'title' => ['sometimes', 'string', 'max:255'],
            'duration_label' => ['nullable', 'string', 'max:64'],
            'organization' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'first_installment' => ['nullable', 'numeric', 'min:0'],
            'second_installment' => ['nullable', 'numeric', 'min:0'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'exam_mode' => ['nullable', 'string', Rule::in($modes)],
            'validity_years' => ['nullable', 'integer', 'min:0'],
            'level' => ['nullable', 'string', 'max:120'],
            'process_flow_id' => ['nullable', 'integer', 'exists:process_flows,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function toDto(): CertificationOfferDto
    {
        $validated = $this->validated();
        $validated['provided_keys'] = array_keys($validated);

        return CertificationOfferDto::fromArray($validated);
    }
}
