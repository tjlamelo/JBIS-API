<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Catalog\Models\Company;
use App\Core\Domain\Identity\Concerns\AuditedModel;
use App\Core\Domain\Identity\Models\UserDocument;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends AuditedModel
{
    use SoftDeletes;

    protected $table = 'interviews';

    protected $fillable = [
        'application_id',
        'application_step_id',
        'company_id',
        'scheduled_date',
        'duration',
        'interview_type',
        'location',
        'interviewer_name',
        'status',
        'result',
        'internal_notes',
        'candidate_feedback',
        'evaluation_criteria',
        'salary_offered',
        'salary_negotiated',
        'work_conditions_notes',
        'report_document_id',
    ];

    protected $casts = [
        'application_id' => 'integer',
        'application_step_id' => 'integer',
        'company_id' => 'integer',
        'scheduled_date' => 'datetime',
        'evaluation_criteria' => 'array',
        'salary_offered' => 'decimal:2',
        'salary_negotiated' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function applicationStep(): BelongsTo
    {
        return $this->belongsTo(ApplicationStep::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function reportDocument(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'report_document_id');
    }
}
