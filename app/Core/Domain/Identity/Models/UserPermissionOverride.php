<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Enums\PermissionOverrideEffect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermissionOverride extends Model
{
    protected $table = 'user_permission_overrides';

    protected $fillable = [
        'user_id',
        'permission_name',
        'effect',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'effect' => PermissionOverrideEffect::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
