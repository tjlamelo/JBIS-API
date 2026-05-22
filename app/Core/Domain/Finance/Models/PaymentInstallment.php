<?php

declare(strict_types=1);

namespace App\Core\Domain\Finance\Models;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInstallment extends Model
{
    protected $table = 'payment_installments';

    protected $fillable = [
        'application_id',
        'application_step_id',
        'amount',
        'currency',
        'due_date',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'application_id' => 'integer',
        'application_step_id' => 'integer',
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function applicationStep(): BelongsTo
    {
        return $this->belongsTo(ApplicationStep::class);
    }
}
