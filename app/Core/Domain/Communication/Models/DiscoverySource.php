<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Models;

use Illuminate\Database\Eloquent\Model;

class DiscoverySource extends Model
{
    protected $fillable = [
        'key',
        'label',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
