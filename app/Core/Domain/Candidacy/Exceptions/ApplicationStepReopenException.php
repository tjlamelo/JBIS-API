<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Exceptions;

use RuntimeException;

final class ApplicationStepReopenException extends RuntimeException
{
    public static function notReachable(): self
    {
        return new self('Cette étape n\'a pas encore été atteinte dans le parcours.');
    }

    public static function applicationNotActive(): self
    {
        return new self('Le parcours doit être démarré pour modifier l\'étape courante.');
    }
}
