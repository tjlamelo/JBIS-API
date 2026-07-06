<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import\DTOs;

final readonly class ProcessFlowImportStepData
{
    /**
     * @param  array{fr: string, en: string}  $title
     * @param  list<string>  $acceptedBanks
     * @param  list<string>  $documentTypeCodes
     */
    public function __construct(
        public int $stepOrder,
        public int $globalStepOrder,
        public string $stepType,
        public ?string $paymentType,
        public ?string $responsibleParty,
        public array $title,
        public float $amount,
        public bool $isBlocking,
        public bool $isRequired,
        public array $acceptedBanks,
        public array $documentTypeCodes,
        public ?int $estimatedDurationDays = null,
        public string $currency = 'XAF',
    ) {}
}
