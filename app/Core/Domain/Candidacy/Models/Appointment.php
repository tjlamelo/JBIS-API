<?php

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Catalog\Models\Agency;
use App\Core\Domain\Communication\Models\DiscoverySource;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    // Si tu gardes le nom de table 'rendez_vous'
    protected $table = 'appointments';

    protected $fillable = [
        'user_id',
        'agency_id',
        'contact_reason',
        'full_name',
        'email',
        'phone',
        'scheduled_at',
        'duration_minutes',
        'subject',
        'message',
        'discovery_source_id',
        'discovery_source_other',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'ip_address',
        'user_agent',
        'type',
        'meeting_link',
        'status',
        'internal_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'user_id' => 'integer',
        'agency_id' => 'integer',
    ];

    /**
     * L'utilisateur qui a pris le rendez-vous (si connecté)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * L'agence JBIS où le rendez-vous doit avoir lieu
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function discoverySource(): BelongsTo
    {
        return $this->belongsTo(DiscoverySource::class);
    }

    /**
     * Scope pour filtrer les rendez-vous à venir
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
            ->where('status', 'CONFIRMED')
            ->orderBy('scheduled_at', 'asc');
    }

    /**
     * Vérifie si le rendez-vous est en ligne
     */
    public function isOnline(): bool
    {
        return $this->type === 'ONLINE';
    }
}
