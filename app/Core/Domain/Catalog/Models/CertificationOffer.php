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
        'duration_label',
        'organization',
        'description',
        'cost',
        'first_installment',
        'second_installment',
        'registration_fee',
        'currency',
        'exam_mode',
        'validity_years',
        'level',
        'process_flow_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'first_installment' => 'decimal:2',
        'second_installment' => 'decimal:2',
        'registration_fee' => 'decimal:2',
        'validity_years' => 'integer',
        'sort_order' => 'integer',
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
