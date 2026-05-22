<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Exceptions;

use RuntimeException;

final class ApplicationDocumentException extends RuntimeException
{
    public static function stepMismatch(): self
    {
        return new self('Cette étape n’appartient pas au dossier.');
    }

    public static function stepDoesNotRequireDocuments(): self
    {
        return new self('Cette étape n’exige pas de pièces justificatives.');
    }

    public static function userDocumentNotFound(): self
    {
        return new self('Document introuvable dans votre bibliothèque.');
    }

    public static function documentTypeNotAllowed(): self
    {
        return new self('Ce type de document n’est pas accepté pour cette étape.');
    }
}
