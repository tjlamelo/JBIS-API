<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\DTOs\ProcessStep;

use App\Core\Domain\Workflow\Mappers\Shared\TranslatablePayloadNormalizer;

readonly class ProcessStepDto
{
    /**
     * @param  list<string>  $provided_keys
     * @param  array{fr?: string, en?: string}  $title
     * @param  array{fr?: string, en?: string}|null  $description
     * @param  list<string>|null  $accepted_banks
     * @param  list<int>|null  $document_type_ids
     */
    public function __construct(
        public array $provided_keys,
        public int $process_flow_id,
        public ?int $process_flow_section_id = null,
        public string $step_type = 'INFO',
        public ?string $payment_type = null,
        public string $responsible_party = 'CANDIDATE',
        public array $title = ['fr' => '', 'en' => ''],
        public ?array $description = null,
        public ?string $internal_note = null,
        public int $step_order = 1,
        public bool $is_blocking = true,
        public bool $is_required = true,
        public string $default_amount = '0',
        public ?array $accepted_banks = null,
        public bool $requires_documents = false,
        public ?array $document_type_ids = null,
        public ?int $estimated_duration_days = null,
        public ?int $sla_alert_days = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = $data['provided_keys'] ?? array_keys($data);

        return new self(
            provided_keys: array_values($providedKeys),
            process_flow_id: (int) ($data['process_flow_id'] ?? 0),
            process_flow_section_id: isset($data['process_flow_section_id']) && $data['process_flow_section_id'] !== ''
                ? (int) $data['process_flow_section_id']
                : null,
            step_type: (string) ($data['step_type'] ?? 'INFO'),
            payment_type: isset($data['payment_type']) && $data['payment_type'] !== ''
                ? (string) $data['payment_type']
                : null,
            responsible_party: (string) ($data['responsible_party'] ?? 'CANDIDATE'),
            title: TranslatablePayloadNormalizer::normalize($data['title'] ?? ''),
            description: isset($data['description'])
                ? TranslatablePayloadNormalizer::normalizeNullable($data['description'])
                : null,
            internal_note: isset($data['internal_note']) ? (string) $data['internal_note'] : null,
            step_order: max(1, (int) ($data['step_order'] ?? 1)),
            is_blocking: (bool) ($data['is_blocking'] ?? true),
            is_required: (bool) ($data['is_required'] ?? true),
            default_amount: is_numeric($data['default_amount'] ?? null) ? (string) $data['default_amount'] : '0',
            accepted_banks: is_array($data['accepted_banks'] ?? null) ? $data['accepted_banks'] : null,
            requires_documents: (bool) ($data['requires_documents'] ?? false),
            document_type_ids: is_array($data['document_type_ids'] ?? null)
                ? array_values(array_map('intval', $data['document_type_ids']))
                : null,
            estimated_duration_days: isset($data['estimated_duration_days'])
                ? (int) $data['estimated_duration_days']
                : null,
            sla_alert_days: isset($data['sla_alert_days']) ? (int) $data['sla_alert_days'] : null,
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
