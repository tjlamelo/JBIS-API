<?php

namespace App\Core\Domain\Communication\Exceptions;

use DomainException;

class EmptyAudienceException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Aucun destinataire trouve pour ce ciblage.');
    }
}
