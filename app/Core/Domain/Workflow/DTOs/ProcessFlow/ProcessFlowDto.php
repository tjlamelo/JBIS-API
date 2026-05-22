<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\DTOs\ProcessFlow;

use App\Core\Domain\Workflow\Mappers\Shared\TranslatablePayloadNormalizer;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;

readonly class ProcessFlowDto
{
    /**
     * @param  list<string>  $provided_keys
     * @param  array{fr?: string, en?: string}  $name
     * @param  array{fr?: string, en?: string}|null  $description
     * @param  list<array<string, mixed>>  $sections  sections avec clé steps[]
     */
    public function __construct(
        public array $provided_keys,
        public array $name,
        public ?string $flow_group_id = null,
        public int $version = 1,
        public string $status = 'draft',
        public ?array $description = null,
        public ?int $program_id = null,
        public ?int $offer_id = null,
        public ?int $country_id = null,
        public ?int $estimated_duration_days = null,
        public string $total_procedure_fees = '0',
        public string $file_opening_fee = '0',
        public ?string $internal_notes = null,
        public array $sections = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = $data['provided_keys'] ?? array_keys($data);
        $status = (string) ($data['status'] ?? ProcessFlowStatus::Draft->value);
        if (ProcessFlowStatus::tryFrom($status) === null) {
            $status = ProcessFlowStatus::Draft->value;
        }

        return new self(
            provided_keys: array_values($providedKeys),
            name: TranslatablePayloadNormalizer::normalize($data['name'] ?? []),
            flow_group_id: isset($data['flow_group_id']) && $data['flow_group_id'] !== ''
                ? (string) $data['flow_group_id']
                : null,
            version: max(1, (int) ($data['version'] ?? 1)),
            status: $status,
            description: isset($data['description'])
                ? TranslatablePayloadNormalizer::normalizeNullable($data['description'])
                : null,
            program_id: self::nullableInt($data, 'program_id'),
            offer_id: self::nullableInt($data, 'offer_id'),
            country_id: self::nullableInt($data, 'country_id'),
            estimated_duration_days: isset($data['estimated_duration_days']) && $data['estimated_duration_days'] !== ''
                ? (int) $data['estimated_duration_days']
                : null,
            total_procedure_fees: is_numeric($data['total_procedure_fees'] ?? null)
                ? (string) $data['total_procedure_fees']
                : '0',
            file_opening_fee: is_numeric($data['file_opening_fee'] ?? null)
                ? (string) $data['file_opening_fee']
                : '0',
            internal_notes: isset($data['internal_notes']) ? (string) $data['internal_notes'] : null,
            sections: is_array($data['sections'] ?? null) ? $data['sections'] : [],
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function nullableInt(array $data, string $key): ?int
    {
        if (! array_key_exists($key, $data)) {
            return null;
        }
        $value = $data[$key];
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
