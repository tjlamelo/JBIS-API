<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs\CertificationOffer;

use App\Core\Domain\Catalog\States\CertificationExamMode;

readonly class CertificationOfferDto
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
        public string $cost = '0',
        public string $currency = 'XAF',
        public string $exam_mode = 'ONSITE',
        public ?int $validity_years = null,
        public ?string $level = null,
        public ?int $process_flow_id = null,
        public bool $is_active = true,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = $data['provided_keys'] ?? array_keys($data);
        $examRaw = isset($data['exam_mode']) ? (string) $data['exam_mode'] : CertificationExamMode::Onsite->value;
        $examMode = CertificationExamMode::tryFrom($examRaw)?->value ?? CertificationExamMode::Onsite->value;

        $cost = '0';
        if (array_key_exists('cost', $data) && $data['cost'] !== null && $data['cost'] !== '') {
            $cost = is_numeric($data['cost']) ? (string) $data['cost'] : '0';
        }

        return new self(
            provided_keys: array_values($providedKeys),
            domain: (string) ($data['domain'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            organization: (string) ($data['organization'] ?? ''),
            description: isset($data['description']) && $data['description'] !== '' ? (string) $data['description'] : null,
            cost: $cost,
            currency: isset($data['currency']) && $data['currency'] !== '' ? (string) $data['currency'] : 'XAF',
            exam_mode: $examMode,
            validity_years: array_key_exists('validity_years', $data) && $data['validity_years'] !== null && $data['validity_years'] !== ''
                ? (int) $data['validity_years']
                : null,
            level: isset($data['level']) && $data['level'] !== '' ? (string) $data['level'] : null,
            process_flow_id: array_key_exists('process_flow_id', $data)
                ? (($data['process_flow_id'] ?? null) !== null && $data['process_flow_id'] !== ''
                    ? (int) $data['process_flow_id']
                    : null)
                : null,
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
