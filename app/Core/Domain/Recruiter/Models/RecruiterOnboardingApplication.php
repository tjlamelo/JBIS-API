<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterOnboardingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruiterOnboardingApplication extends Model
{
    protected $fillable = [
        'applicant_user_id',
        'company_name',
        'legal_form',
        'registration_number',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address',
        'website',
        'activity_description',
        'desired_slug',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'staff_note',
        'rejection_reason',
        'recruiter_organization_id',
    ];

    protected $casts = [
        'status' => RecruiterOnboardingStatus::class,
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_user_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(RecruiterOrganization::class, 'recruiter_organization_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RecruiterOnboardingDocument::class);
    }

    public function isEditableByApplicant(): bool
    {
        return in_array($this->status, [
            RecruiterOnboardingStatus::Draft,
            RecruiterOnboardingStatus::NeedsChanges,
        ], true);
    }
}
