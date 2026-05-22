<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Exceptions;

final class PdfConfigurationException extends PdfException
{
    public static function missingCredentials(): self
    {
        return new self(
            'iLovePDF credentials are missing. Set ILOVEPDF_PUBLIC_KEY and '
            .'ILOVEPDF_SECRET_KEY in your .env (see config/ilovepdf.php).'
        );
    }
}
