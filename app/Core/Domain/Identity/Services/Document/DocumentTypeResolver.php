<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

use App\Core\Domain\Identity\Exceptions\Document\InvalidUserDocumentTypeException;
use App\Core\Domain\Identity\Models\DocumentType;

final class DocumentTypeResolver
{
    public function resolveByCode(string $code): DocumentType
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            throw InvalidUserDocumentTypeException::unknown($code);
        }

        $type = DocumentType::query()
            ->where('code', $normalized)
            ->where('is_active', true)
            ->first();

        if ($type === null) {
            throw InvalidUserDocumentTypeException::unknown($code);
        }

        return $type;
    }

    public function resolveById(int $id): DocumentType
    {
        $type = DocumentType::query()
            ->where('is_active', true)
            ->find($id);

        if ($type === null) {
            throw InvalidUserDocumentTypeException::unknown((string) $id);
        }

        return $type;
    }

    /**
     * @param  int|string  $input  Code (PASSPORT) ou id numérique
     */
    public function resolve(int|string $input): DocumentType
    {
        if (is_int($input) || ctype_digit((string) $input)) {
            return $this->resolveById((int) $input);
        }

        return $this->resolveByCode((string) $input);
    }
}
