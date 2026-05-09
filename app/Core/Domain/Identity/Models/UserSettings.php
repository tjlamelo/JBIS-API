<?php

namespace App\Core\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSetting extends Model
{
    use HasFactory;

    protected $table = 'user_settings';

    /**
     * Les attributs qui peuvent être assignés massivement.
     */
    protected $fillable = [
        'user_id',
        'language',
        'theme',
        'timezone',
        'notifications',
        'privacy',
        'marketing',
    ];

    /**
     * Le cast des attributs pour un typage automatique.
     */
    protected $casts = [
        'user_id' => 'integer',
        'notifications' => 'array',
        'privacy' => 'array',
        'marketing' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valeurs par défaut pour les attributs.
     */
    protected $attributes = [
        'theme' => 'light',
        'language' => 'fr',
        'timezone' => 'Africa/Douala',
        'notifications' => '[]',
        'privacy' => '[]',
        'marketing' => '[]',
    ];

    /**
     * Relation avec l'utilisateur propriétaire.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}