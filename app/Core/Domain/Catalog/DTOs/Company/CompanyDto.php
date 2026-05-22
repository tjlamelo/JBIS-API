<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs\Company;

use App\Core\Domain\Catalog\States\CompanyStatus;
use App\Core\Domain\Catalog\States\CompanyType;

readonly class CompanyDto
{
    /**
     * @param  list<string>  $provided_keys
     */
    public function __construct(
        public array $provided_keys,
        public string $name,
        public ?string $slug = null,
        public ?int $offer_category_id = null,
        public ?int $country_id = null,
        public ?int $city_id = null,
        public ?string $address = null,
        public string $type = 'EMPLOYER',
        public string $status = 'DRAFT',
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $website = null,
        public ?string $description = null,
        public ?string $logo = null,
        public bool $is_approved = false,
        public ?int $approved_by = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = $data['provided_keys'] ?? array_keys($data);
        $typeRaw = isset($data['type']) ? (string) $data['type'] : CompanyType::Employer->value;
        $type = CompanyType::tryFrom($typeRaw)?->value ?? CompanyType::Employer->value;
        $statusRaw = isset($data['status']) ? (string) $data['status'] : CompanyStatus::Draft->value;
        $status = CompanyStatus::tryFrom($statusRaw)?->value ?? CompanyStatus::Draft->value;

        return new self(
            provided_keys: array_values($providedKeys),
            name: (string) ($data['name'] ?? ''),
            slug: isset($data['slug']) && $data['slug'] !== '' ? (string) $data['slug'] : null,
            offer_category_id: array_key_exists('offer_category_id', $data) && $data['offer_category_id'] !== null && $data['offer_category_id'] !== ''
                ? (int) $data['offer_category_id']
                : null,
            country_id: array_key_exists('country_id', $data) && $data['country_id'] !== null && $data['country_id'] !== ''
                ? (int) $data['country_id']
                : null,
            city_id: array_key_exists('city_id', $data) && $data['city_id'] !== null && $data['city_id'] !== ''
                ? (int) $data['city_id']
                : null,
            address: isset($data['address']) && $data['address'] !== '' ? (string) $data['address'] : null,
            type: $type,
            status: $status,
            email: isset($data['email']) && $data['email'] !== '' ? (string) $data['email'] : null,
            phone: isset($data['phone']) && $data['phone'] !== '' ? (string) $data['phone'] : null,
            website: isset($data['website']) && $data['website'] !== '' ? (string) $data['website'] : null,
            description: isset($data['description']) && $data['description'] !== '' ? (string) $data['description'] : null,
            logo: isset($data['logo']) && $data['logo'] !== '' ? (string) $data['logo'] : null,
            is_approved: (bool) ($data['is_approved'] ?? false),
            approved_by: array_key_exists('approved_by', $data) && $data['approved_by'] !== null && $data['approved_by'] !== ''
                ? (int) $data['approved_by']
                : null,
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
