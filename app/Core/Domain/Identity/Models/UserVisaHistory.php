<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditsModelChanges;
use App\Core\Domain\Identity\States\VisaHistoryStatus;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserVisaHistory extends Model
{
    use AuditsModelChanges, SoftDeletes;

    protected $table = 'user_visa_histories';

    protected $fillable = [
        'user_id',
        'country_id',
        'visa_type',
        'visa_number',
        'status',
        'issue_date',
        'expiry_date',
        'rejection_reason',
        'rejection_date',
        'document_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => VisaHistoryStatus::class,
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'rejection_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'document_id');
    }
}
