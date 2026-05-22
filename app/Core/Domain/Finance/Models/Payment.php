<?php

declare(strict_types=1);

namespace App\Core\Domain\Finance\Models;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'application_id',
        'application_step_id',
        'user_id',
        'amount',
        'currency',
        'payment_type',
        'payment_method',
        'payment_date',
        'due_date',
        'status',
        'transaction_id',
        'reference',
        'description',
    ];

    protected $casts = [
        'application_id' => 'integer',
        'application_step_id' => 'integer',
        'user_id' => 'integer',
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function applicationStep(): BelongsTo
    {
        return $this->belongsTo(ApplicationStep::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
