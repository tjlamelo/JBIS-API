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
        'priority',
        'progress_percentage',
        'status',
        'final_result',
    ];

    protected $casts = [
        'due_date' => 'date',
        'progress_percentage' => 'integer',
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

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withTimestamps();
    }

    public function dailyTasks(): HasMany
    {
        return $this->hasMany(DailyTask::class);
    }
}
