<?php

namespace App\Core\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Certification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'certifications';

    protected $fillable = [
        'user_id',
        'name',
        'issuing_organization',
        'document_id',
        'issue_date',
        'expiry_date',
        'credential_id',
        'credential_url',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'user_id' => 'integer',
        'document_id' => 'integer',
    ];

    /**
     * Relation vers le document (Scan de la certification)
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'document_id');
    }

    /**
     * Relation vers l'utilisateur (Propriétaire)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers l'admin qui a validé
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Vérifier si la certification est encore valide
     */
    public function isValid(): bool
    {
        if (!$this->expiry_date) return true;
        return $this->expiry_date->isFuture();
    }
}