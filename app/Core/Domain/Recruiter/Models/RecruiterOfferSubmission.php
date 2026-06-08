<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Models;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterOfferSubmissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterOfferSubmission extends Model
{
    protected $fillable = [
        'recruiter_organization_id',
        'submitted_by_user_id',
        'offer_id',
        'status',
        'payload',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'staff_note',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => RecruiterOfferSubmissionStatus::class,
        'payload' => 'array',
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

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isEditableByRecruiter(): bool
    {
        return in_array($this->status, [
            RecruiterOfferSubmissionStatus::Draft,
            RecruiterOfferSubmissionStatus::NeedsChanges,
        ], true);
    }
}
