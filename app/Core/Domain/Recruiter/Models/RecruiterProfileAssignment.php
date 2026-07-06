<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Enums\RecruiterSharedProfileSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterProfileAssignment extends Model
{
    protected $fillable = [
        'recruiter_organization_id',
        'candidate_user_id',
        'assigned_by_user_id',
        'status',
        'note',
        'visible_sections',
        'assigned_at',
        'revoked_at',
    ];

    protected $casts = [
        'status' => RecruiterAssignmentStatus::class,
        'visible_sections' => 'array',
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(RecruiterOrganization::class, 'recruiter_organization_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /**
     * @return list<string>
     */
    public function resolvedVisibleSections(): array
    {
        return RecruiterSharedProfileSection::normalize($this->visible_sections);
    }
}
