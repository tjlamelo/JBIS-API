<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserInternship;
use App\Core\Domain\Partner\Enums\PartnerCohortStudentEnrollmentStatus;
use App\Core\Domain\Partner\Enums\PartnerCohortStudentPlacementStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerCohortStudent extends Model
{
    protected $fillable = [
        'partner_cohort_id',
        'student_user_id',
        'invited_email',
        'invited_name',
        'enrollment_status',
        'placement_status',
        'user_internship_id',
        'partner_notes',
        'staff_notes',
        'enrolled_at',
        'enrolled_by_user_id',
    ];

    protected $casts = [
        'enrollment_status' => PartnerCohortStudentEnrollmentStatus::class,
        'placement_status' => PartnerCohortStudentPlacementStatus::class,
        'enrolled_at' => 'datetime',
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(PartnerCohort::class, 'partner_cohort_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function internship(): BelongsTo
    {
        return $this->belongsTo(UserInternship::class, 'user_internship_id');
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by_user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PartnerCohortStudentDocument::class);
    }
}
