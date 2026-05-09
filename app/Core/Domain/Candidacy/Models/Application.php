<?php

namespace App\Core\Domain\Candidacy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';

    /**
     * Champs remplissables
     */
    protected $fillable = [
        'user_id',
        'program_id',
        'offer_id',
        'application_number',  // Identifiant unique
        'status',              // Enum: PENDING, IN_PROGRESS, APPROVED, REJECTED, CANCELLED
        'notes',               // Notes internes
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(\App\Core\Domain\Identity\Models\User::class);
    }

    public function program()
    {
        return $this->belongsTo(\App\Core\Domain\Catalog\Models\Program::class);
    }

    public function offer()
    {
        return $this->belongsTo(\App\Core\Domain\Catalog\Models\Offer::class);
    }
}
