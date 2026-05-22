<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Exceptions;

use RuntimeException;

final class ApplicationStepAdvanceException extends RuntimeException
{
    public static function notPending(): self
    {
        return new self('Seule une étape en cours (PENDING) peut être avancée.');
    }

    public static function paymentRequired(): self
    {
        return new self('Le paiement de cette étape doit être soldé avant de continuer.');
    }

    public static function documentsNotValidated(): self
    {
        return new self('Les documents de cette étape doivent être validés par le staff.');
    }

    public static function interviewNotPassed(): self
    {
        return new self('L\'entretien doit être validé (réussi) avant de continuer.');
    }

    public static function signatureRequired(): self
    {
        return new self('La signature est requise pour cette étape.');
    }
}
