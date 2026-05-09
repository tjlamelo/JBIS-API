<?php
declare(strict_types=1);

namespace App\Core\User\Dto;

use App\Core\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

class CertificationDto implements IDto
{
    public function __construct(
        public readonly string $certificationName,
        public readonly string $issuingOrganization,
        public readonly ?string $issueDate,        // accepte null
        public readonly ?string $expiryDate,       // accepte null
        public readonly ?string $certificationFile,
        public ?bool $is_approved = null,
        public ?int $approved_by = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->input('certification_name', ''),
            $request->input('issuing_organization', ''),
            $request->input('issue_date') ?: null,
            $request->input('expiry_date') ?: null,
            $request->input('certification_file', ''),
            $request->boolean('is_approved'),
            $request->filled('approved_by') ? (int) $request->input('approved_by') : null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['certification_name'] ?? '',
            $data['issuing_organization'] ?? '',
            $data['issue_date'] ?? null,
            $data['expiry_date'] ?? null,
            $data['certification_file'] ?? '',
            $data['is_approved'] ?? null,
            $data['approved_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'certification_name'   => $this->certificationName,
            'issuing_organization' => $this->issuingOrganization,
            'issue_date'           => $this->issueDate,
            'expiry_date'         => $this->expiryDate,
            'certification_file'   => $this->certificationFile,
            'is_approved'         => $this->is_approved,
            'approved_by'         => $this->approved_by,
        ];
    }
}
