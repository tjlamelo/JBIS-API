<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Exceptions;

final class UnsupportedFormatException extends ExportException
{
    public static function forFormat(string $format): self
    {
        return new self("Le format d'export « {$format} » n'est pas pris en charge.");
    }
}
