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
        'is_outside_meeting',
        'title',
        'description',
        'task_date',
        'hours_spent',
        'minutes_spent',
        'status',
        'blockers_notes',
    ];

    protected $casts = [
        'task_date' => 'date',
        'hours_spent' => 'integer',
        'minutes_spent' => 'integer',
        'is_outside_meeting' => 'boolean',
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

    public function totalMinutes(): int
    {
        if ($this->minutes_spent !== null && (int) $this->minutes_spent > 0) {
            return (int) $this->minutes_spent;
        }

        return ((int) ($this->hours_spent ?? 0)) * 60;
    }
}
