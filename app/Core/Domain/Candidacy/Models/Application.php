<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Finance\Models\Payment;
use App\Core\Domain\Finance\Models\PaymentSchedule;
use App\Core\Domain\Identity\Concerns\AuditedModel;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends AuditedModel
{
    use SoftDeletes;

    protected $table = 'applications';

    protected $fillable = [
        'application_number',
        'user_id',
        'offer_id',
        'program_id',
        'process_flow_id',
        'flow_group_id',
        'process_flow_version',
        'protocol_document_id',
        'has_accepted_protocol',
        'protocol_accepted_at',
        'protocol_acceptance_ip',
        'status',
        'is_private',
        'created_by',
        'current_application_step_id',
        'total_due',
        'total_paid',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'offer_id' => 'integer',
        'program_id' => 'integer',
        'process_flow_id' => 'integer',
        'process_flow_version' => 'integer',
        'has_accepted_protocol' => 'boolean',
        'protocol_accepted_at' => 'datetime',
        'status' => ApplicationStatus::class,
        'is_private' => 'boolean',
        'created_by' => 'integer',
        'current_application_step_id' => 'integer',
        'total_due' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function processFlow(): BelongsTo
    {
        return $this->belongsTo(ProcessFlow::class);
    }

    public function protocolDocument(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'protocol_document_id');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(ApplicationStep::class, 'current_application_step_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApplicationStep::class)->orderBy('step_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApplicationStepEvent::class)->orderByDesc('created_at');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function languageCourses(): HasMany
    {
        return $this->hasMany(CandidateLanguageCourse::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentSchedule(): HasOne
    {
        return $this->hasOne(PaymentSchedule::class);
    }

    public function totalRemaining(): float
    {
        return max(0, (float) $this->total_due - (float) $this->total_paid);
    }
}
