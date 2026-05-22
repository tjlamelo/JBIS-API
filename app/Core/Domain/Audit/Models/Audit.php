<?php

declare(strict_types=1);

namespace App\Core\Domain\Audit\Models;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Models\Audit as AuditModel;

class Audit extends AuditModel
{
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
