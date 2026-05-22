<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Screenshot\Exceptions;

use RuntimeException;

final class ScreenshotServiceException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('Service screenshot : SCREENSHOT_SERVICE_URL et SCREENSHOT_SERVICE_TOKEN requis.');
    }

    public static function requestFailed(int $status, string $detail = ''): self
    {
        $message = "Service screenshot : échec HTTP {$status}.";
        if ($detail !== '') {
            $message .= ' '.$detail;
        }

        return new self($message);
    }

    public static function unreachable(string $detail = ''): self
    {
        $message = 'Service screenshot injoignable.';
        if ($detail !== '') {
            $message .= ' '.$detail;
        }

        return new self($message);
    }
}
