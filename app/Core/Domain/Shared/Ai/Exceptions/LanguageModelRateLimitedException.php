<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Exceptions;

/**
 * Erreur transitoire (RPM / 429 / 503) — le job doit retry / release.
 */
final class LanguageModelRateLimitedException extends LanguageModelTransportException
{
    public function __construct(
        string $message,
        public readonly ?int $retryAfterSeconds = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
