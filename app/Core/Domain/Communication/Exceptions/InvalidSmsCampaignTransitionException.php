<?php

namespace App\Core\Domain\Communication\Exceptions;

use DomainException;

class InvalidSmsCampaignTransitionException extends DomainException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Transition de statut invalide: {$from} -> {$to}");
    }
}
