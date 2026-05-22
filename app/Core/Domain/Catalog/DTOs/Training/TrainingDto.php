<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs\Training;

use App\Core\Domain\Catalog\States\TrainingDeliveryMode;

readonly class TrainingDto
{
    /**
     * @param  list<string>  $provided_keys
     */
    public function __construct(
        public array $provided_keys,
        public string $domain,
        public string $title,
        public string $organization,
        public ?string $description = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?int $duration_hours = null,
        public ?int $duration_days = null,
        public string $mode = 'ONLINE',
        public ?string $location = null,
        public ?string $prerequisites = null,
        public bool $is_certified = false,
        public ?string $certificate_name = null,
        public bool $is_active = true,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = $data['provided_keys'] ?? array_keys($data);
        $modeRaw = isset($data['mode']) ? (string) $data['mode'] : TrainingDeliveryMode::Online->value;
        $mode = TrainingDeliveryMode::tryFrom($modeRaw)?->value ?? TrainingDeliveryMode::Online->value;

        return new self(
            provided_keys: array_values($providedKeys),
            domain: (string) ($data['domain'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            organization: (string) ($data['organization'] ?? ''),
            description: isset($data['description']) ? ($data['description'] !== '' ? (string) $data['description'] : null) : null,
            start_date: isset($data['start_date']) && $data['start_date'] !== '' && $data['start_date'] !== null
                ? (string) $data['start_date']
                : null,
            end_date: isset($data['end_date']) && $data['end_date'] !== '' && $data['end_date'] !== null
                ? (string) $data['end_date']
                : null,
            duration_hours: array_key_exists('duration_hours', $data) && $data['duration_hours'] !== null && $data['duration_hours'] !== ''
                ? (int) $data['duration_hours']
                : null,
            duration_days: array_key_exists('duration_days', $data) && $data['duration_days'] !== null && $data['duration_days'] !== ''
                ? (int) $data['duration_days']
                : null,
            mode: $mode,
            location: isset($data['location']) && $data['location'] !== '' ? (string) $data['location'] : null,
            prerequisites: isset($data['prerequisites']) && $data['prerequisites'] !== '' ? (string) $data['prerequisites'] : null,
            is_certified: (bool) ($data['is_certified'] ?? false),
            certificate_name: isset($data['certificate_name']) && $data['certificate_name'] !== '' ? (string) $data['certificate_name'] : null,
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
