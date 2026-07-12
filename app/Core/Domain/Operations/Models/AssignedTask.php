<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Operations\Enums\AssignedTaskPriority;
use App\Core\Domain\Operations\Enums\AssignedTaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignedTask extends Model
{
    protected $fillable = [
        'meeting_id',
        'created_by',
        'title',
        'description',
        'due_date',
        'estimated_minutes',
        'minutes_spent',
        'week_start_date',
        'priority',
        'progress_percentage',
        'status',
        'final_result',
        'started_at',
        'completed_at',
        'renewed_from_id',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'week_start_date' => 'date',
        'estimated_minutes' => 'integer',
        'minutes_spent' => 'integer',
        'progress_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'priority' => AssignedTaskPriority::class,
        'status' => AssignedTaskStatus::class,
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'renewed_from_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withTimestamps();
    }

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(DailyTask::class);
    }

    public function isOverdue(): bool
    {
        if ($this->due_date === null) {
            return false;
        }

        if (in_array($this->status, [AssignedTaskStatus::Done, AssignedTaskStatus::Cancelled], true)) {
            return false;
        }

        return $this->due_date->isPast();
    }
}
