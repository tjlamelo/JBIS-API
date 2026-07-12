<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Operations\Enums\DailyTaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTask extends Model
{
    protected $fillable = [
        'user_id',
        'assigned_task_id',
        'title',
        'description',
        'task_date',
        'hours_spent',
        'status',
        'blockers_notes',
    ];

    protected $casts = [
        'task_date' => 'date',
        'hours_spent' => 'integer',
        'status' => DailyTaskStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTask(): BelongsTo
    {
        return $this->belongsTo(AssignedTask::class);
    }
}
