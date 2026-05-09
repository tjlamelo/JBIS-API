<?php

namespace App\Core\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConsent extends Model
{
    // Pas de colonnes updated_at, car un consentement ne se modifie pas, il se rajoute
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'version',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'user_id' => 'integer',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}