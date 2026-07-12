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
        public ?int $trade_id,
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
        public bool $allows_public_applications = true,
        public ?string $work_mode = null,
        public int $available_positions = 1,
        public ?string $language = null,
        public ?array $meta = null,

        // Relations
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
            provided_keys: [],
            id: $request->filled('id') ? (int) $request->input('id') : null,
            trade_id: $request->integer('trade_id') ?: null,
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
            allows_public_applications: $request->boolean('allows_public_applications', true),
            work_mode: $request->input('work_mode'),
            available_positions: $request->input('available_positions', 1),
            language: $request->input('language'),
            meta: $request->input('meta', []),
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
     * Attributs prêts pour Eloquent : `photo` est un accessor, seule `photo_media` est persistée.
     *
     * @return array<string, mixed>
     */
    public function toPersistedArray(): array
    {
        $attributes = $this->toArray();
        $photoProvided = in_array('photo', $this->provided_keys, true)
            || ($this->provided_keys === [] && array_key_exists('photo', $attributes));
        $mediaProvided = in_array('photo_media', $this->provided_keys, true)
            || ($this->provided_keys === [] && array_key_exists('photo_media', $attributes));

        $photo = $attributes['photo'] ?? null;
        unset($attributes['photo']);

        $photoUrl = is_string($photo) ? trim($photo) : '';
        $media = $attributes['photo_media'] ?? null;
        $hasUsableMedia = is_array($media)
            && (
                (isset($media['public_url']) && is_string($media['public_url']) && trim($media['public_url']) !== '')
                || (isset($media['local_optimized_path']) && trim((string) $media['local_optimized_path']) !== '')
                || (isset($media['local_raw_path']) && trim((string) $media['local_raw_path']) !== '')
                || (isset($media['cloudinary_id']) && trim((string) $media['cloudinary_id']) !== '')
            );

        if ($hasUsableMedia) {
            return $attributes;
        }

        if ($photoUrl !== '') {
            $attributes['photo_media'] = self::mediaFromPublicUrl($photoUrl);

            return $attributes;
        }

        if ($mediaProvided || $photoProvided) {
            $attributes['photo_media'] = null;
        }

        return $attributes;
    }

    /**
     * @return array{
     *     file_name: string,
     *     local_optimized_path: string,
     *     local_raw_path: string,
     *     cloudinary_id: null,
     *     public_url: string,
     *     is_primary: bool
     * }
     */
    public static function mediaFromPublicUrl(string $url): array
    {
        $path = parse_url($url, PHP_URL_PATH);
        $fileName = is_string($path) && $path !== '' ? basename($path) : 'offer-photo';

        return [
            'file_name' => $fileName !== '' ? $fileName : 'offer-photo',
            'local_optimized_path' => '',
            'local_raw_path' => '',
            'cloudinary_id' => null,
            'public_url' => $url,
            'is_primary' => true,
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = array_keys($data);

        $photo = null;
        if (array_key_exists('photo', $data)) {
            if (is_string($data['photo'])) {
                $photo = $data['photo'];
            } elseif (is_array($data['photo'])) {
                $url = $data['photo']['url'] ?? null;
                $photo = is_string($url) ? $url : null;
            }
        }

        return new self(
            provided_keys: $providedKeys,
            id: isset($data['id']) ? (int) $data['id'] : null,
            trade_id: isset($data['trade_id']) ? (int) $data['trade_id'] : null,
            description: isset($data['description']) && is_array($data['description']) ? $data['description'] : null,
            photo: $photo,
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
            allows_public_applications: array_key_exists('allows_public_applications', $data)
                ? (bool) $data['allows_public_applications']
                : true,
            work_mode: isset($data['work_mode']) ? (string) $data['work_mode'] : null,
            available_positions: (int) ($data['available_positions'] ?? 1),
            language: $data['language'] ?? null,
            meta: isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : null,
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
