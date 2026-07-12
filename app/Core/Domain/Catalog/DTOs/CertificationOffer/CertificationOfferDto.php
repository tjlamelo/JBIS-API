<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs\CertificationOffer;

use App\Core\Domain\Catalog\States\CertificationExamMode;

readonly class CertificationOfferDto
{
    /**
     * @param  list<string>  $provided_keys
     * @param  array{fr?: string, en?: string}|string  $title
     * @param  array{fr?: string, en?: string}|string|null  $duration_label
     * @param  array{fr?: string, en?: string}|string  $organization
     * @param  array{fr?: string, en?: string}|string|null  $description
     * @param  array{fr?: string, en?: string}|string|null  $level
     */
    public function __construct(
        public array $provided_keys,
        public string $domain,
        public array|string $title,
        public array|string|null $duration_label = null,
        public array|string $organization = ['fr' => 'JBIS', 'en' => 'JBIS'],
        public array|string|null $description = null,
        public string $cost = '0',
        public ?string $first_installment = null,
        public ?string $second_installment = null,
        public string $registration_fee = '25000',
        public string $currency = 'XAF',
        public string $exam_mode = 'ONSITE',
        public ?int $validity_years = null,
        public array|string|null $level = null,
        public ?int $process_flow_id = null,
        public int $sort_order = 0,
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

        $firstInstallment = null;
        if (array_key_exists('first_installment', $data) && $data['first_installment'] !== null && $data['first_installment'] !== '') {
            $firstInstallment = is_numeric($data['first_installment']) ? (string) $data['first_installment'] : null;
        }

        $secondInstallment = null;
        if (array_key_exists('second_installment', $data) && $data['second_installment'] !== null && $data['second_installment'] !== '') {
            $secondInstallment = is_numeric($data['second_installment']) ? (string) $data['second_installment'] : null;
        }

        $registrationFee = '25000';
        if (array_key_exists('registration_fee', $data) && $data['registration_fee'] !== null && $data['registration_fee'] !== '') {
            $registrationFee = is_numeric($data['registration_fee']) ? (string) $data['registration_fee'] : '25000';
        }

        return new self(
            provided_keys: array_values($providedKeys),
            domain: (string) ($data['domain'] ?? 'AMCA'),
            title: self::localized($data['title'] ?? ''),
            duration_label: array_key_exists('duration_label', $data)
                ? self::localizedNullable($data['duration_label'])
                : null,
            organization: self::localized($data['organization'] ?? ['fr' => 'JBIS', 'en' => 'JBIS']),
            description: array_key_exists('description', $data)
                ? self::localizedNullable($data['description'])
                : null,
            cost: $cost,
            first_installment: $firstInstallment,
            second_installment: $secondInstallment,
            registration_fee: $registrationFee,
            currency: isset($data['currency']) && $data['currency'] !== '' ? (string) $data['currency'] : 'XAF',
            exam_mode: $examMode,
            validity_years: array_key_exists('validity_years', $data) && $data['validity_years'] !== null && $data['validity_years'] !== ''
                ? (int) $data['validity_years']
                : null,
            level: array_key_exists('level', $data)
                ? self::localizedNullable($data['level'])
                : null,
            process_flow_id: array_key_exists('process_flow_id', $data)
                ? (($data['process_flow_id'] ?? null) !== null && $data['process_flow_id'] !== ''
                    ? (int) $data['process_flow_id']
                    : null)
                : null,
            sort_order: array_key_exists('sort_order', $data) && $data['sort_order'] !== null && $data['sort_order'] !== ''
                ? (int) $data['sort_order']
                : 0,
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }

    /**
     * @return array{fr: string, en: string}
     */
    private static function localized(mixed $value): array
    {
        if (is_array($value)) {
            return [
                'fr' => trim((string) ($value['fr'] ?? '')),
                'en' => trim((string) ($value['en'] ?? '')),
            ];
        }

        $text = trim((string) $value);

        return ['fr' => $text, 'en' => $text];
    }

    /**
     * @return array{fr: string, en: string}|null
     */
    private static function localizedNullable(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $localized = self::localized($value);
        if ($localized['fr'] === '' && $localized['en'] === '') {
            return null;
        }

        return $localized;
    }
}
