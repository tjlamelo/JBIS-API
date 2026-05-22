<?php

declare(strict_types=1);

namespace App\Core\Domain\Finance\Models;

use App\Core\Domain\Candidacy\Models\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSchedule extends Model
{
    protected $table = 'payment_schedules';

    protected $fillable = [
        'application_id',
        'total_amount',
        'paid_amount',
    ];

    protected $casts = [
        'application_id' => 'integer',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
