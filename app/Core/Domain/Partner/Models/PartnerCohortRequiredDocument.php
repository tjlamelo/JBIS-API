<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerCohortRequiredDocument extends Model
{
    protected $fillable = [
        'partner_cohort_id',
        'document_type_code',
        'is_mandatory',
        'sort_order',
        'label_override',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'label_override' => 'array',
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(PartnerCohort::class, 'partner_cohort_id');
    }
}
