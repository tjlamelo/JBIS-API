<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\DTOs;

readonly class OfferApplicationReadiness
{
    /**
     * @param  list<string>  $blocking_reasons
     * @param  list<array<string, mixed>>  $required_documents
     */
    public function __construct(
        public bool $can_apply,
        public string $offer_status,
        public bool $offer_accepting_applications,
        public ?array $existing_application,
        public array $required_documents,
        public array $blocking_reasons,
        public string $recommended_application_status,
        public bool $has_process_flow,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'can_apply' => $this->can_apply,
            'offer_status' => $this->offer_status,
            'offer_accepting_applications' => $this->offer_accepting_applications,
            'existing_application' => $this->existing_application,
            'required_documents' => $this->required_documents,
            'blocking_reasons' => $this->blocking_reasons,
            'recommended_application_status' => $this->recommended_application_status,
            'has_process_flow' => $this->has_process_flow,
        ];
    }
}
