<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterOnboardingDocument extends Model
{
    protected $fillable = [
        'recruiter_onboarding_application_id',
        'document_type',
        'original_filename',
        'storage_path',
        'mime_type',
        'size_bytes',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(RecruiterOnboardingApplication::class, 'recruiter_onboarding_application_id');
    }
}
