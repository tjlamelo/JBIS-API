<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\DTOs\Document;

use App\Core\Domain\Identity\Exceptions\Document\InvalidUserDocumentTypeException;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Services\Document\DocumentTypeResolver;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;

final readonly class UserDocumentDto
{
    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public int $userId,
        public DocumentType $documentType,
        public ?string $documentNumber = null,
        public ?int $issuingCountryId = null,
        public ?string $issueDate = null,
        public ?string $expiryDate = null,
        public ?string $notes = null,
        public bool $isVerifiedCopy = false,
        public bool $isSensitive = false,
        public ?int $uploadedBy = null,
        public UserDocumentStatus $status = UserDocumentStatus::Pending,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, int $userId, ?int $uploadedBy = null): self
    {
        $resolver = app(DocumentTypeResolver::class);

        if (! isset($data['type']) && ! isset($data['document_type_id'])) {
            throw InvalidUserDocumentTypeException::unknown('');
        }

        try {
            if (isset($data['document_type_id'])) {
                $documentType = $resolver->resolveById((int) $data['document_type_id']);
            } else {
                $documentType = $resolver->resolve((string) $data['type']);
            }
        } catch (InvalidUserDocumentTypeException $e) {
            throw $e;
        } catch (\Throwable) {
            throw InvalidUserDocumentTypeException::unknown((string) ($data['type'] ?? $data['document_type_id'] ?? ''));
        }

        $status = $data['status'] ?? UserDocumentStatus::Pending->value;
        if ($status instanceof UserDocumentStatus) {
            $enumStatus = $status;
        } else {
            $enumStatus = UserDocumentStatus::tryFrom((string) $status) ?? UserDocumentStatus::Pending;
        }

        return new self(
            userId: $userId,
            documentType: $documentType,
            documentNumber: isset($data['document_number']) ? (string) $data['document_number'] : null,
            issuingCountryId: isset($data['issuing_country_id']) ? (int) $data['issuing_country_id'] : null,
            issueDate: isset($data['issue_date']) ? (string) $data['issue_date'] : null,
            expiryDate: isset($data['expiry_date']) ? (string) $data['expiry_date'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            isVerifiedCopy: (bool) ($data['is_verified_copy'] ?? false),
            isSensitive: (bool) ($data['is_sensitive'] ?? false),
            uploadedBy: $uploadedBy,
            status: $enumStatus,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'user_id' => $this->userId,
            'uploaded_by' => $this->uploadedBy,
            'document_type_id' => $this->documentType->id,
            'document_number' => $this->documentNumber,
            'issuing_country_id' => $this->issuingCountryId,
            'issue_date' => $this->issueDate,
            'expiry_date' => $this->expiryDate,
            'notes' => $this->notes,
            'is_verified_copy' => $this->isVerifiedCopy,
            'is_sensitive' => $this->isSensitive,
            'status' => $this->status->value,
        ];
    }
}
