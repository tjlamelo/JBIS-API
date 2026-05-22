<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactRequest extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'message',
        'discovery_source_id',
        'discovery_source_other',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'ip_address',
        'user_agent',
    ];

    public function discoverySource(): BelongsTo
    {
        return $this->belongsTo(DiscoverySource::class);
    }
}
