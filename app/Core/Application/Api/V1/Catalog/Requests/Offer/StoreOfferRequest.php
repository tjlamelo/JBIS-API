<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\Offer;

use App\Core\Domain\Catalog\DTOs\Offer\OfferDto;
use App\Core\Domain\Catalog\States\OfferStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOfferRequest extends FormRequest
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
            // --- CHAMPS MULTILINGUES (SPATIE) ---
            'trade_id' => ['required', 'integer', 'exists:trades,id'],

            'description' => ['nullable', 'array'],
            'description.fr' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'photo' => ['nullable', 'string', 'max:2048'],
            'photo_media' => ['nullable', 'array'],

            'responsibilities' => ['nullable', 'array'],
            'responsibilities.fr' => ['nullable', 'string'],
            'responsibilities.en' => ['nullable', 'string'],

            'requirements' => ['nullable', 'array'],
            'requirements.fr' => ['nullable', 'string'],
            'requirements.en' => ['nullable', 'string'],

            'specific_documents' => ['nullable', 'array'],
            'specific_documents.fr' => ['nullable', 'string'],
            'specific_documents.en' => ['nullable', 'string'],

            // --- CHAMPS STANDARDS ---
            'address' => ['required', 'string', 'max:255'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_salary_public' => ['nullable', 'boolean'],
            'is_company_public' => ['nullable', 'boolean'],
            'work_mode' => ['nullable', 'string', 'max:50'],
            'available_positions' => ['nullable', 'integer', 'min:1'],
            'region' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:10'],
            'meta' => ['nullable', 'array'],
            'meta.is_featured' => ['nullable', 'boolean'],
            'meta.is_urgent' => ['nullable', 'boolean'],
            'meta.seo' => ['nullable', 'array'],
            'meta.seo.title' => ['nullable', 'string', 'max:255'],
            'meta.seo.description' => ['nullable', 'string'],
            'meta.seo.robots' => ['nullable', 'string', 'max:255'],

            // --- CLÉS ÉTRANGÈRES ---
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'contract_type_id' => ['nullable', 'integer', 'exists:contract_types,id'],
            'offer_type_id' => ['nullable', 'integer', 'exists:offer_types,id'],
            'work_schedule_id' => ['nullable', 'integer', 'exists:work_schedules,id'],
            'education_level_id' => ['nullable', 'integer', 'exists:education_levels,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'benefit_ids' => ['nullable', 'array'],
            'benefit_ids.*' => ['integer', 'exists:benefits,id'],
            'language_requirements' => ['nullable', 'array'],
            'language_requirements.*.language_id' => ['required_with:language_requirements', 'integer', 'exists:languages,id'],
            'language_requirements.*.language_level_id' => ['nullable', 'integer', 'exists:language_levels,id'],
            'language_requirements.*.required_level' => ['nullable', 'string', 'max:100'],
            'skill_requirements' => ['nullable', 'array'],
            'skill_requirements.*.skill_id' => ['required_with:skill_requirements', 'integer', 'exists:skills,id'],
            'skill_requirements.*.level' => ['nullable', 'string', 'max:50'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*.required_document_id' => ['required_with:required_documents', 'integer', 'exists:required_documents,id'],
            'required_documents.*.is_mandatory' => ['nullable', 'boolean'],
            'required_documents.*.sort_order' => ['nullable', 'integer', 'min:0'],

            // --- PARAMÈTRES DE PUBLICATION ---
            'status' => ['nullable', new Enum(OfferStatus::class)],
            'published_at' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:published_at'],
        ];
    }

    public function toDto(): OfferDto
    {
        $validated = $this->validated();
        $validated['user_id'] = (int) $this->user()?->id;

        return OfferDto::fromArray($validated);
    }
}
