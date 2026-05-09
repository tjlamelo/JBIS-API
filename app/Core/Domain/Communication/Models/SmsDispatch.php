<?php

namespace App\Core\Domain\Communication\Models;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsDispatch extends Model
{
    protected $fillable = [
        'sms_campaign_id',
        'user_id',
        'phone_number',
        'status',
        'provider_message_id',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
