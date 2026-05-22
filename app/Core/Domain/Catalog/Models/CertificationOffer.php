<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Workflow\Models\ProcessFlow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationOffer extends Model
{
    protected $table = 'certification_offers';

    protected $fillable = [
        'domain',
        'title',
        'organization',
        'description',
        'cost',
        'currency',
        'exam_mode',
        'validity_years',
        'level',
        'process_flow_id',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'validity_years' => 'integer',
        'is_active' => 'boolean',
    ];

    public function processFlow(): BelongsTo
    {
        return $this->belongsTo(ProcessFlow::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
