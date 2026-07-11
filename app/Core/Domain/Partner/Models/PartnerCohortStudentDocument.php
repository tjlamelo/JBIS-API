<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Partner\Enums\PartnerCohortStudentDocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerCohortStudentDocument extends Model
{
    protected $fillable = [
        'partner_cohort_student_id',
        'document_type_code',
        'user_document_id',
        'status',
        'validated_by',
        'validated_at',
        'staff_note',
    ];

    protected $casts = [
        'status' => PartnerCohortStudentDocumentStatus::class,
        'validated_at' => 'datetime',
    ];

    public function cohortStudent(): BelongsTo
    {
        return $this->belongsTo(PartnerCohortStudent::class, 'partner_cohort_student_id');
    }

    public function userDocument(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
