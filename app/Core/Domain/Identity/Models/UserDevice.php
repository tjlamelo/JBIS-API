<?php

namespace App\Core\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_key',
        'personal_access_token_id',
        'device_name',
        'ip',
        'last_ip',
        'user_agent',
        'is_trusted',
        'last_login_at',
        'login_count',
        'risk_score',
        'risk_flags',
        'first_seen_at',
        'last_seen_at',
        'last_alerted_at',
        'last_token_name',
    ];

    protected $casts = [
        'is_trusted' => 'boolean',
        'risk_flags' => 'array',
        'last_login_at' => 'datetime',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_alerted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
