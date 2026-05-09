<?php

namespace App\Core\Domain\Communication\Models;

use App\Core\Domain\Communication\Exceptions\InvalidSmsCampaignTransitionException;
use App\Core\Domain\Communication\States\SmsCampaignStatus;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsCampaign extends Model
{
    protected $fillable = [
        'created_by',
        'name',
        'message',
        'targeting',
        'send_mode',
        'sender_id',
        'recipients_count',
        'sent_count',
        'failed_count',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'targeting' => 'array',
        'sent_at' => 'datetime',
        'status' => SmsCampaignStatus::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(SmsDispatch::class);
    }

    public function transitionTo(SmsCampaignStatus $to): void
    {
        $from = $this->status instanceof SmsCampaignStatus ? $this->status : SmsCampaignStatus::Draft;
        $allowed = $from->allowedTransitions();

        if (! in_array($to, $allowed, true) && $from !== $to) {
            throw new InvalidSmsCampaignTransitionException($from->value, $to->value);
        }

        $this->update([
            'status' => $to->value,
        ]);
    }
}
