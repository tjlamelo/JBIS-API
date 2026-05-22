<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Exceptions;

final class InvalidFieldException extends ExportException
{
    public static function forKey(string $sourceKey, string $fieldKey): self
    {
        return new self("Le champ « {$fieldKey} » n'est pas exportable pour la source « {$sourceKey} ».");
    }
}
