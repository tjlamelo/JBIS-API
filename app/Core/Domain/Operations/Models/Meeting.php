<?php

declare(strict_types=1);

namespace App\Core\Domain\Operations\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Operations\Enums\MeetingStatus;
use App\Core\Domain\Operations\Enums\MeetingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = [
        'type',
        'title',
        'location',
        'scheduled_at',
        'duration_minutes',
        'agenda',
        'minutes',
        'decisions',
        'organizer_id',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'type' => MeetingType::class,
        'status' => MeetingStatus::class,
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_user')
            ->withPivot(['is_present', 'excuse_reason'])
            ->withTimestamps();
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(AssignedTask::class);
    }
}
