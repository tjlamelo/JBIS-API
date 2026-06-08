<?php

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditedModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterestAndHobby extends AuditedModel
{
    use HasFactory;

    protected $table = 'interests_and_hobbies';

    protected $fillable = [
        'user_id',
        'title',
    ];

    protected $casts = [
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
