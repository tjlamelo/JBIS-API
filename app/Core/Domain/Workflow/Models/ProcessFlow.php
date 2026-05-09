<?php

namespace App\Core\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessFlow extends Model
{
    /**
     * Les colonnes qui peuvent être remplies en masse
     */
    protected $fillable = [
        'program_id',
        'offer_id',
        'total_steps',
    ];

    /**
     * Relation vers le programme associé
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Catalog\Models\Program::class);
    }

    /**
     * Relation vers l'offre d'emploi associée
     */
    public function Offer(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Catalog\Models\Offer::class);
    }
}
