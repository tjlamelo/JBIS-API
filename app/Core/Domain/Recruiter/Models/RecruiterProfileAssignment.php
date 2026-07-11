<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Enums\RecruiterMaskedField;
use App\Core\Domain\Recruiter\Enums\RecruiterSharedProfileSection;
use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterProfileAssignment extends Model
{
    protected $fillable = [
        'recruiter_organization_id',
        'candidate_user_id',
        'assigned_by_user_id',
        'recruiter_profile_request_id',
        'status',
        'feedback_status',
        'note',
        'feedback_note',
        'feedback_updated_at',
        'feedback_updated_by_user_id',
        'visible_sections',
        'masked_fields',
        'assigned_at',
        'revoked_at',
    ];

    protected $casts = [
        'status' => RecruiterAssignmentStatus::class,
        'feedback_updated_at' => 'datetime',
        'visible_sections' => 'array',
        'masked_fields' => 'array',
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

    public function feedbackUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'feedback_updated_by_user_id');
    }

    public function profileRequest(): BelongsTo
    {
        return $this->belongsTo(RecruiterProfileRequest::class, 'recruiter_profile_request_id');
    }

    /**
     * @return list<string>
     */
    public function resolvedVisibleSections(): array
    {
        return RecruiterSharedProfileSection::normalize($this->visible_sections);
    }

    /**
     * @return list<string>
     */
    public function resolvedMaskedFields(): array
    {
        return RecruiterMaskedField::normalize($this->masked_fields);
    }
}
