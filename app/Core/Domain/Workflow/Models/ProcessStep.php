<?php

namespace App\Core\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessStep extends Model
{
    protected $table = 'process_steps';

    // Champs pouvant être assignés en masse
    protected $fillable = [
        'process_flow_id',
        'step_order',
        'step_name',
        'step_description',
        'payment_required',
        'estimated_duration',
    ];

    /**
     * Relation vers le ProcessFlow auquel cette étape appartient
     */
    public function processFlow(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Workflow\Models\ProcessFlow::class);
    }
}
