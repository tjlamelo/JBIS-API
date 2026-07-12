<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Exceptions;

use RuntimeException;

final class ApplicationEnrollmentException extends RuntimeException
{
    /** @var list<string> */
    private array $reasons = [];

    public static function missingTarget(): self
    {
        return new self('Une offre ou un programme est requis pour créer une candidature.');
    }

    public static function processFlowNotFound(): self
    {
        return new self('Aucun Process Flow publié pour cette offre ou ce programme.');
    }

    public static function processFlowHasNoSteps(): self
    {
        return new self('Le Process Flow publié ne contient aucune étape.');
    }

    /**
     * @param  list<string>  $reasons
     */
    public static function notEligible(array $reasons): self
    {
        $message = $reasons !== []
            ? implode(' ', $reasons)
            : 'Vous ne pouvez pas postuler à cette offre pour le moment.';

        $exception = new self($message);
        $exception->reasons = array_values($reasons);

        return $exception;
    }

    /**
     * @return list<string>
     */
    public function reasons(): array
    {
        return $this->reasons !== [] ? $this->reasons : [$this->getMessage()];
    }
}
