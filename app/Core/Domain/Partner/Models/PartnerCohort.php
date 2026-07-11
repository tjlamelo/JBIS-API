<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Partner\Enums\PartnerCohortStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerCohort extends Model
{
    protected $fillable = [
        'partner_organization_id',
        'name',
        'academic_year',
        'field_of_study',
        'start_date',
        'end_date',
        'status',
        'expected_student_count',
        'description',
        'submitted_at',
        'submitted_by_user_id',
        'reviewed_by',
        'reviewed_at',
        'staff_note',
        'rejection_reason',
        'settings',
    ];

    protected $casts = [
        'status' => PartnerCohortStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'settings' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(PartnerOrganization::class, 'partner_organization_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function students(): HasMany
    {
        return $this->hasMany(PartnerCohortStudent::class);
    }

    public function requiredDocuments(): HasMany
    {
        return $this->hasMany(PartnerCohortRequiredDocument::class);
    }

    public function isEditableByPartner(): bool
    {
        return in_array($this->status, [
            PartnerCohortStatus::Draft,
            PartnerCohortStatus::Rejected,
        ], true);
    }
}
