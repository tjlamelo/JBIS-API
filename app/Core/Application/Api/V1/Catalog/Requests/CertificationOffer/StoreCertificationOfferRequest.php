<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\CertificationOffer;

use App\Core\Domain\Catalog\DTOs\CertificationOffer\CertificationOfferDto;
use App\Core\Domain\Catalog\Models\CertificationOffer;
use App\Core\Domain\Catalog\States\CertificationExamMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCertificationOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CertificationOffer::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $modes = array_map(static fn (CertificationExamMode $m) => $m->value, CertificationExamMode::cases());

        return [
            'domain' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'array'],
            'title.fr' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'duration_label' => ['nullable', 'array'],
            'duration_label.fr' => ['nullable', 'string', 'max:64'],
            'duration_label.en' => ['nullable', 'string', 'max:64'],
            'organization' => ['nullable', 'array'],
            'organization.fr' => ['nullable', 'string', 'max:255'],
            'organization.en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'first_installment' => ['nullable', 'numeric', 'min:0'],
            'second_installment' => ['nullable', 'numeric', 'min:0'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'exam_mode' => ['nullable', 'string', Rule::in($modes)],
            'validity_years' => ['nullable', 'integer', 'min:0'],
            'level' => ['nullable', 'array'],
            'level.fr' => ['nullable', 'string', 'max:120'],
            'level.en' => ['nullable', 'string', 'max:120'],
            'process_flow_id' => ['nullable', 'integer', 'exists:process_flows,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function toDto(): CertificationOfferDto
    {
        $validated = $this->validated();
        $validated['provided_keys'] = array_keys($validated);
        $validated['domain'] = $validated['domain'] ?? 'AMCA';
        $validated['organization'] = $validated['organization'] ?? ['fr' => 'JBIS', 'en' => 'JBIS'];

        return CertificationOfferDto::fromArray($validated);
    }
}
