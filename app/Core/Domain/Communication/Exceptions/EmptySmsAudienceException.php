<?php

namespace App\Core\Domain\Communication\Exceptions;

use DomainException;

class EmptySmsAudienceException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Aucun numero de telephone valide trouve pour ce ciblage.');
    }
}
