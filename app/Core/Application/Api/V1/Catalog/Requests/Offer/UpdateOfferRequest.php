<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Requests\Offer;

use App\Core\Domain\Catalog\DTOs\Offer\OfferDto;
use App\Core\Domain\Catalog\States\OfferStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOfferRequest extends FormRequest
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
            'trade_id' => ['sometimes', 'integer', 'exists:trades,id'],
            'description' => ['sometimes', 'nullable', 'array'],
            'description.fr' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'photo' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'photo_media' => ['sometimes', 'nullable', 'array'],
            'photo_media.file_name' => ['nullable', 'string', 'max:255'],
            'photo_media.local_optimized_path' => ['nullable', 'string', 'max:500'],
            'photo_media.local_raw_path' => ['nullable', 'string', 'max:500'],
            'photo_media.cloudinary_id' => ['nullable', 'string', 'max:255'],
            'photo_media.public_url' => ['nullable', 'string', 'max:2048'],
            'photo_media.is_primary' => ['nullable', 'boolean'],
            'photo_media.uploaded_at' => ['nullable', 'string', 'max:50'],
            'salary_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'salary_max' => ['sometimes', 'nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'is_salary_public' => ['sometimes', 'nullable', 'boolean'],
            'is_company_public' => ['sometimes', 'nullable', 'boolean'],
            'allows_public_applications' => ['sometimes', 'nullable', 'boolean'],
            'work_mode' => ['sometimes', 'nullable', 'string', 'max:50'],
            'contract_type_id' => ['sometimes', 'nullable', 'integer', 'exists:contract_types,id'],
            'offer_type_id' => ['sometimes', 'nullable', 'integer', 'exists:offer_types,id'],
            'work_schedule_id' => ['sometimes', 'nullable', 'integer', 'exists:work_schedules,id'],
            'education_level_id' => ['sometimes', 'nullable', 'integer', 'exists:education_levels,id'],
            'available_positions' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'country_id' => ['sometimes', 'nullable', 'integer', 'exists:countries,id'],
            'city_id' => ['sometimes', 'nullable', 'integer', 'exists:cities,id'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'benefit_ids' => ['sometimes', 'array'],
            'benefit_ids.*' => ['integer', 'exists:benefits,id'],
            'specific_documents' => ['sometimes', 'nullable', 'array'],
            'specific_documents.fr' => ['nullable', 'string'],
            'specific_documents.en' => ['nullable', 'string'],
            'responsibilities' => ['sometimes', 'nullable', 'array'],
            'responsibilities.fr' => ['nullable', 'string'],
            'responsibilities.en' => ['nullable', 'string'],
            'requirements' => ['sometimes', 'nullable', 'array'],
            'requirements.fr' => ['nullable', 'string'],
            'requirements.en' => ['nullable', 'string'],
            'language' => ['sometimes', 'nullable', 'string', 'max:10'],
            'meta' => ['sometimes', 'nullable', 'array'],
            'meta.is_featured' => ['nullable', 'boolean'],
            'meta.is_urgent' => ['nullable', 'boolean'],
            'meta.seo' => ['nullable', 'array'],
            'meta.seo.title' => ['nullable', 'string', 'max:255'],
            'meta.seo.description' => ['nullable', 'string'],
            'meta.seo.robots' => ['nullable', 'string', 'max:255'],
            'language_requirements' => ['sometimes', 'array'],
            'language_requirements.*.language_id' => ['required_with:language_requirements', 'integer', 'exists:languages,id'],
            'language_requirements.*.language_level_id' => ['nullable', 'integer', 'exists:language_levels,id'],
            'language_requirements.*.required_level' => ['nullable', 'string', 'max:100'],
            'skill_requirements' => ['sometimes', 'array'],
            'skill_requirements.*.skill_id' => ['required_with:skill_requirements', 'integer', 'exists:skills,id'],
            'skill_requirements.*.level' => ['nullable', 'string', 'max:50'],
            'required_documents' => ['sometimes', 'array'],
            'required_documents.*.required_document_id' => ['required_with:required_documents', 'integer', 'exists:required_documents,id'],
            'required_documents.*.is_mandatory' => ['nullable', 'boolean'],
            'required_documents.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'company_id' => ['sometimes', 'nullable', 'integer', 'exists:companies,id'],
            'program_id' => ['sometimes', 'nullable', 'integer', 'exists:programs,id'],
            'status' => ['sometimes', 'nullable', new Enum(OfferStatus::class)],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'expiration_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:published_at'],
        ];
    }

    public function toDto(): OfferDto
    {
        return OfferDto::fromArray($this->validated());
    }
}
