<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Exceptions\Document;

use RuntimeException;

final class UserDocumentLockedException extends RuntimeException
{
    public static function forActiveApplication(): self
    {
        return new self(__('Ce document est lié à une candidature en cours et ne peut pas être modifié ou supprimé. Annulez la candidature pour le libérer.'));
    }

    public static function validatedByStaff(): self
    {
        return new self(__('Ce document a été validé par l\'équipe JBIS. Seul un administrateur peut le modifier ou le supprimer.'));
    }
}
