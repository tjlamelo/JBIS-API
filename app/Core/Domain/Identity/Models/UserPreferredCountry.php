<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditedModel;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreferredCountry extends AuditedModel
{
    protected $table = 'user_preferred_countries';

    protected $fillable = [
        'user_id',
        'country_id',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
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
}
