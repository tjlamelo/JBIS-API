<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Exceptions;

final class UnknownSourceException extends ExportException
{
    public static function forKey(string $key): self
    {
        return new self("Source d'export inconnue : « {$key} ».");
    }
}
