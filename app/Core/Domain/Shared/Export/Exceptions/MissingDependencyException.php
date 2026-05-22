<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Exceptions;

final class MissingDependencyException extends ExportException
{
    public static function forPackage(string $format, string $package): self
    {
        return new self(
            "Le format « {$format} » nécessite l'installation du paquet Composer « {$package} ». "
            ."Exécutez : composer require {$package}"
        );
    }
}
