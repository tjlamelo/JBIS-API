<?php

namespace App\Core\Domain\Communication\Models;

use App\Core\Domain\Communication\Exceptions\InvalidMailCampaignTransitionException;
use App\Core\Domain\Communication\States\MailCampaignStatus;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailCampaign extends Model
{
    protected $fillable = [
        'created_by',
        'name',
        'subject',
        'body',
        'content',
        'targeting',
        'send_mode',
        'from_name',
        'reply_to',
        'recipients_count',
        'sent_count',
        'failed_count',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'targeting' => 'array',
        'content' => 'array',
        'sent_at' => 'datetime',
        'status' => MailCampaignStatus::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(MailDispatch::class);
    }

    public function transitionTo(MailCampaignStatus $to): void
    {
        $from = $this->status instanceof MailCampaignStatus ? $this->status : MailCampaignStatus::Draft;
        $allowed = $from->allowedTransitions();

        if (! in_array($to, $allowed, true) && $from !== $to) {
            throw new InvalidMailCampaignTransitionException($from->value, $to->value);
        }

        $this->update([
            'status' => $to->value,
        ]);
    }
}
