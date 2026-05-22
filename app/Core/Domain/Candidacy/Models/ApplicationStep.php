<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Candidacy\States\ApplicationStepPaymentStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use App\Core\Domain\Finance\Models\Payment;
use App\Core\Domain\Finance\Models\PaymentInstallment;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationStep extends Model
{
    protected $table = 'application_steps';

    protected $fillable = [
        'application_id',
        'process_step_id',
        'step_order',
        'section_key',
        'step_type',
        'payment_type',
        'responsible_party',
        'title',
        'description',
        'is_blocking',
        'is_required',
        'requires_documents',
        'document_type_ids',
        'amount_due',
        'amount_paid',
        'payment_status',
        'documents_validated',
        'documents_validated_at',
        'documents_validated_by',
        'interview_passed',
        'interview_validated_at',
        'interview_validated_by',
        'is_signed',
        'signed_at',
        'signed_contract_id',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'application_id' => 'integer',
        'process_step_id' => 'integer',
        'step_order' => 'integer',
        'step_type' => ProcessStepType::class,
        'title' => 'array',
        'description' => 'array',
        'is_blocking' => 'boolean',
        'is_required' => 'boolean',
        'requires_documents' => 'boolean',
        'document_type_ids' => 'array',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'payment_status' => ApplicationStepPaymentStatus::class,
        'status' => ApplicationStepStatus::class,
        'documents_validated' => 'boolean',
        'documents_validated_at' => 'datetime',
        'interview_passed' => 'boolean',
        'interview_validated_at' => 'datetime',
        'is_signed' => 'boolean',
        'signed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function processStep(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PaymentInstallment::class);
    }

    public function applicationDocuments(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function interview(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Interview::class);
    }

    public function documentsValidatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'documents_validated_by');
    }

    public function interviewValidatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interview_validated_by');
    }

    public function signedContract(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'signed_contract_id');
    }

    public function amountRemaining(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }
}
