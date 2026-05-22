<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSecurityEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_device_id',
        'event',
        'risk_score',
        'risk_level',
        'signals',
        'ip',
        'user_agent',
        'meta',
        'occurred_at',
    ];

    protected $casts = [
        'signals' => 'array',
        'meta' => 'array',
        'occurred_at' => 'datetime',
        'risk_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class, 'user_device_id');
    }
}
