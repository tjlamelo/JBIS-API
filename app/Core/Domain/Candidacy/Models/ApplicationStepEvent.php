<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStepEvent extends Model
{
    public $timestamps = false;

    protected $table = 'application_step_events';

    protected $fillable = [
        'application_id',
        'application_step_id',
        'actor_user_id',
        'action',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'application_id' => 'integer',
        'application_step_id' => 'integer',
        'actor_user_id' => 'integer',
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function applicationStep(): BelongsTo
    {
        return $this->belongsTo(ApplicationStep::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
