<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Exceptions\Document;

use InvalidArgumentException;

final class InvalidUserDocumentTypeException extends InvalidArgumentException
{
    public static function unknown(string $value): self
    {
        return new self(__(
            'Type de document invalide « :value ». Consultez le catalogue document_types.',
            ['value' => $value],
        ));
    }
}
