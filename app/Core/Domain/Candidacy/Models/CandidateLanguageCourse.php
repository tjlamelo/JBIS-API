<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Training;
use App\Core\Domain\Candidacy\States\LanguageCourseStatus;
use App\Core\Domain\Identity\Concerns\AuditedModel;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserTraining;
use App\Core\Domain\Location\Models\Language;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateLanguageCourse extends AuditedModel
{
    protected $table = 'candidate_language_courses';

    protected $fillable = [
        'user_id',
        'offer_id',
        'application_id',
        'language_id',
        'training_id',
        'user_training_id',
        'status',
        'target_level',
        'achieved_level',
        'observations',
        'staff_notes',
        'started_at',
        'completed_at',
        'recorded_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'offer_id' => 'integer',
        'application_id' => 'integer',
        'language_id' => 'integer',
        'training_id' => 'integer',
        'user_training_id' => 'integer',
        'recorded_by' => 'integer',
        'status' => LanguageCourseStatus::class,
        'started_at' => 'date',
        'completed_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function userTraining(): BelongsTo
    {
        return $this->belongsTo(UserTraining::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
