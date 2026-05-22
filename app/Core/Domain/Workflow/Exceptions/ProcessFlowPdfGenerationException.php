<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Exceptions;

use RuntimeException;

final class ProcessFlowPdfGenerationException extends RuntimeException
{
    public static function chromeUnavailable(string $detail = ''): self
    {
        $message = 'PDF Process Flow : génération indisponible.';
        if ($detail !== '') {
            $message .= ' '.$detail;
        }

        return new self($message);
    }
}
