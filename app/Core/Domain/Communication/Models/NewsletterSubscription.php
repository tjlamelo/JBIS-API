<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Models;

use App\Core\Domain\Communication\Enums\NewsletterScope;
use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSubscription extends Model
{
    protected $fillable = [
        'email',
        'user_id',
        'name',
        'language',
        'scope',
        'status',
        'unsubscribe_token',
        'source',
        'subscribed_at',
        'unsubscribed_at',
        'last_sent_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'scope' => NewsletterScope::class,
        'status' => NewsletterSubscriptionStatus::class,
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === NewsletterSubscriptionStatus::Subscribed;
    }
}
