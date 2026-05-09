<?php

namespace App\Core\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_documents';

    protected $fillable = [
        'user_id',
        'type',
        'document_number',
        'description',
        'files',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'status',
        'rejection_reason',
        'validated_at',
        'validated_by',
    ];

    protected $casts = [
        'files' => 'array',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'validated_at' => 'datetime',
        'user_id' => 'integer',
    ];

    /**
     * Accessor pour récupérer l'URL du Recto (Front)
     */
    public function getFrontUrlAttribute(): ?string
    {
        $path = $this->files['front'] ?? null;
        return $path ? Storage::url($path) : null;
    }

    /**
     * Accessor pour récupérer l'URL du Verso (Back)
     */
    public function getBackUrlAttribute(): ?string
    {
        $path = $this->files['back'] ?? null;
        return $path ? Storage::url($path) : null;
    }

    /**
     * Relation avec l'utilisateur (Propriétaire)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'admin qui a validé
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Vérifier si le document est expiré
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}