<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Concerns;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Modèle Eloquent avec journal d'audit (owen-it/laravel-auditing).
 */
abstract class AuditedModel extends Model implements AuditableContract
{
    use AuditsModelChanges;
}
