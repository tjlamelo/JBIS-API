<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Concerns;

use OwenIt\Auditing\Auditable;

/**
 * Journalise créations / mises à jour / suppressions (table `audits`).
 */
trait AuditsModelChanges
{
    use Auditable;

    /**
     * @var list<string>
     */
    protected $auditExclude = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
