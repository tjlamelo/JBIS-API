<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs\Agency;

readonly class AgencyDto
{
    /**
     * @param  list<string>  $provided_keys
     * @param  array<string, string>  $name
     * @param  array<string, string>|null  $description
     * @param  list<string>|null  $phones
     * @param  list<string>|null  $whatsapp_numbers
     * @param  array<string, mixed>|null  $opening_hours
     */
    public function __construct(
        public array $provided_keys,
        public array $name,
        public string $slug,
        public ?array $description = null,
        public ?int $country_id = null,
        public ?int $city_id = null,
        public ?string $address = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?array $phones = null,
        public ?array $whatsapp_numbers = null,
        public string $email = '',
        public ?int $manager_id = null,
        public int $number_of_employees = 0,
        public ?array $opening_hours = null,
        public ?string $image_url = null,
        public bool $is_active = true,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = $data['provided_keys'] ?? array_keys($data);

        return new self(
            provided_keys: array_values($providedKeys),
            name: is_array($data['name'] ?? null) ? $data['name'] : [],
            slug: (string) ($data['slug'] ?? ''),
            description: is_array($data['description'] ?? null) ? $data['description'] : null,
            country_id: array_key_exists('country_id', $data) && $data['country_id'] !== null && $data['country_id'] !== ''
                ? (int) $data['country_id']
                : null,
            city_id: array_key_exists('city_id', $data) && $data['city_id'] !== null && $data['city_id'] !== ''
                ? (int) $data['city_id']
                : null,
            address: isset($data['address']) && $data['address'] !== '' ? (string) $data['address'] : null,
            latitude: array_key_exists('latitude', $data) && $data['latitude'] !== null && $data['latitude'] !== ''
                ? (float) $data['latitude']
                : null,
            longitude: array_key_exists('longitude', $data) && $data['longitude'] !== null && $data['longitude'] !== ''
                ? (float) $data['longitude']
                : null,
            phones: is_array($data['phones'] ?? null) ? array_values($data['phones']) : null,
            whatsapp_numbers: is_array($data['whatsapp_numbers'] ?? null) ? array_values($data['whatsapp_numbers']) : null,
            email: (string) ($data['email'] ?? ''),
            manager_id: array_key_exists('manager_id', $data) && $data['manager_id'] !== null && $data['manager_id'] !== ''
                ? (int) $data['manager_id']
                : null,
            number_of_employees: max(0, (int) ($data['number_of_employees'] ?? 0)),
            opening_hours: is_array($data['opening_hours'] ?? null) ? $data['opening_hours'] : null,
            image_url: isset($data['image_url']) && $data['image_url'] !== '' ? (string) $data['image_url'] : null,
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
