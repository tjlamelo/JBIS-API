<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs\Offer;

use Illuminate\Http\Request;

readonly class OfferDto
{
    public function __construct(
        /** @var array<int,string> */
        public array $provided_keys,
        public ?int $id,
        public array $title,
        public ?array $description = null,
        public ?string $photo = null,
        public ?array $photo_media = null,
        public ?array $slug = null,

        // Localisation & Détails
        public ?int $city_id = null,
        public ?int $country_id = null,
        public ?string $address = null,
        public ?float $salary_min = null,
        public ?float $salary_max = null,
        public ?string $currency = null,
        public bool $is_salary_public = false,
        public bool $is_company_public = false,
        public ?string $work_mode = null,
        public int $available_positions = 1,
        public ?string $language = null,
        public ?array $meta = null,

        // Relations
        public ?int $offer_category_id = null,
        public ?int $contract_type_id = null,
        public ?int $offer_type_id = null,
        public ?int $work_schedule_id = null,
        public ?int $education_level_id = null,
        public ?int $company_id = null,
        public ?int $program_id = null,
        public ?int $user_id = null,
        public array $benefit_ids = [],
        public array $language_requirements = [], // [{language_id, required_level}]
        public array $skill_requirements = [], // [{skill_id, level}]
        public array $required_documents = [], // [{required_document_id, is_mandatory, sort_order}]

        // Statut & Dates
        public string $status = 'PUBLISHED',
        public ?string $published_at = null,
        public ?string $expiration_date = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            id: $request->filled('id') ? (int) $request->input('id') : null,
            title: $request->input('title', []),
            description: $request->input('description'),
            photo: $request->input('photo'),
            photo_media: $request->input('photo_media'),
            slug: $request->input('slug'),
            city_id: $request->integer('city_id') ?: null,
            country_id: $request->integer('country_id') ?: null,
            address: $request->input('address'),
            salary_min: $request->filled('salary_min') ? (float) $request->input('salary_min') : null,
            salary_max: $request->filled('salary_max') ? (float) $request->input('salary_max') : null,
            currency: $request->input('currency', 'XAF'),
            is_salary_public: $request->boolean('is_salary_public'),
            is_company_public: $request->boolean('is_company_public'),
            work_mode: $request->input('work_mode'),
            available_positions: $request->input('available_positions', 1),
            language: $request->input('language'),
            meta: $request->input('meta', []),
            offer_category_id: $request->integer('offer_category_id') ?: null,
            contract_type_id: $request->integer('contract_type_id') ?: null,
            offer_type_id: $request->integer('offer_type_id') ?: null,
            work_schedule_id: $request->integer('work_schedule_id') ?: null,
            education_level_id: $request->integer('education_level_id') ?: null,
            company_id: $request->integer('company_id') ?: null,
            program_id: $request->integer('program_id') ?: null,
            user_id: (int) ($request->input('user_id') ?? $request->user()?->id),
            benefit_ids: $request->input('benefit_ids', []),
            language_requirements: $request->input('language_requirements', []),
            skill_requirements: $request->input('skill_requirements', []),
            required_documents: $request->input('required_documents', []),
            status: $request->input('status', 'PUBLISHED'),
            published_at: $request->input('published_at'),
            expiration_date: $request->input('expiration_date'),
        );
    }

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['provided_keys']);

        if ($this->provided_keys === []) {
            return array_filter($vars, static fn ($value) => $value !== null);
        }

        $provided = array_flip($this->provided_keys);

        return array_intersect_key($vars, $provided);
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = array_keys($data);

        return new self(
            provided_keys: $providedKeys,
            id: isset($data['id']) ? (int) $data['id'] : null,
            title: (array) ($data['title'] ?? []),
            description: isset($data['description']) && is_array($data['description']) ? $data['description'] : null,
            photo: isset($data['photo']) ? (string) $data['photo'] : null,
            photo_media: array_key_exists('photo_media', $data)
                ? (is_array($data['photo_media']) ? $data['photo_media'] : null)
                : null,
            slug: isset($data['slug']) && is_array($data['slug']) ? $data['slug'] : null,
            city_id: isset($data['city_id']) ? (int) $data['city_id'] : null,
            country_id: isset($data['country_id']) ? (int) $data['country_id'] : null,
            address: $data['address'] ?? null,
            salary_min: isset($data['salary_min']) ? (float) $data['salary_min'] : null,
            salary_max: isset($data['salary_max']) ? (float) $data['salary_max'] : null,
            currency: $data['currency'] ?? null,
            is_salary_public: (bool) ($data['is_salary_public'] ?? false),
            is_company_public: (bool) ($data['is_company_public'] ?? false),
            work_mode: isset($data['work_mode']) ? (string) $data['work_mode'] : null,
            available_positions: (int) ($data['available_positions'] ?? 1),
            language: $data['language'] ?? null,
            meta: isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : null,
            offer_category_id: isset($data['offer_category_id']) ? (int) $data['offer_category_id'] : null,
            contract_type_id: isset($data['contract_type_id']) ? (int) $data['contract_type_id'] : null,
            offer_type_id: isset($data['offer_type_id']) ? (int) $data['offer_type_id'] : null,
            work_schedule_id: isset($data['work_schedule_id']) ? (int) $data['work_schedule_id'] : null,
            education_level_id: isset($data['education_level_id']) ? (int) $data['education_level_id'] : null,
            company_id: isset($data['company_id']) ? (int) $data['company_id'] : null,
            program_id: isset($data['program_id']) ? (int) $data['program_id'] : null,
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            benefit_ids: array_values(array_map('intval', (array) ($data['benefit_ids'] ?? []))),
            language_requirements: (array) ($data['language_requirements'] ?? []),
            skill_requirements: (array) ($data['skill_requirements'] ?? []),
            required_documents: (array) ($data['required_documents'] ?? []),
            status: (string) ($data['status'] ?? 'PUBLISHED'),
            published_at: $data['published_at'] ?? null,
            expiration_date: $data['expiration_date'] ?? null,
        );
    }
}
