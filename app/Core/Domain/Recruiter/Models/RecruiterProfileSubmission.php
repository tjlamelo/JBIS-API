<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterSubmissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterProfileSubmission extends Model
{
    protected $fillable = [
        'recruiter_organization_id',
        'submitted_by_user_id',
        'candidate_user_id',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'staff_note',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => RecruiterSubmissionStatus::class,
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(RecruiterOrganization::class, 'recruiter_organization_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isEditableByRecruiter(): bool
    {
        return in_array($this->status, [
            RecruiterSubmissionStatus::Draft,
            RecruiterSubmissionStatus::NeedsChanges,
        ], true);
    }
}
