<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterProfileRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruiterProfileRequest extends Model
{
    protected $fillable = [
        'recruiter_organization_id',
        'submitted_by_user_id',
        'status',
        'title',
        'criteria',
        'quantity_needed',
        'note',
        'matched_candidate_ids',
        'matched_count',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'staff_note',
        'rejection_reason',
        'transmitted_at',
        'transmitted_by_user_id',
        'transmitted_candidate_ids',
    ];

    protected $casts = [
        'status' => RecruiterProfileRequestStatus::class,
        'criteria' => 'array',
        'matched_candidate_ids' => 'array',
        'transmitted_candidate_ids' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'transmitted_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(RecruiterOrganization::class, 'recruiter_organization_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function transmittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transmitted_by_user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RecruiterProfileAssignment::class, 'recruiter_profile_request_id');
    }

    public function isEditableByRecruiter(): bool
    {
        return in_array($this->status, [
            RecruiterProfileRequestStatus::Draft,
            RecruiterProfileRequestStatus::NeedsChanges,
        ], true);
    }

    public function canBeTransmitted(): bool
    {
        return in_array($this->status, [
            RecruiterProfileRequestStatus::Submitted,
            RecruiterProfileRequestStatus::Matched,
        ], true) && $this->matched_count > 0;
    }
}
