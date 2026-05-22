<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditsModelChanges;
use App\Core\Domain\Catalog\Models\Training;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTraining extends Model
{
    use AuditsModelChanges;

    protected $table = 'user_trainings';

    protected $fillable = [
        'user_id',
        'training_id',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'training_id' => 'integer',
        'started_at' => 'date',
        'finished_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
