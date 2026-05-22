<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ProcessStep extends Model
{
    use HasTranslations;

    protected $table = 'process_steps';

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'process_flow_id',
        'process_flow_section_id',
        'step_type',
        'payment_type',
        'responsible_party',
        'title',
        'description',
        'internal_note',
        'step_order',
        'is_blocking',
        'is_required',
        'default_amount',
        'accepted_banks',
        'requires_documents',
        'document_type_ids',
        'estimated_duration_days',
        'sla_alert_days',
    ];

    protected $casts = [
        'process_flow_id' => 'integer',
        'process_flow_section_id' => 'integer',
        'step_order' => 'integer',
        'is_blocking' => 'boolean',
        'is_required' => 'boolean',
        'default_amount' => 'decimal:2',
        'requires_documents' => 'boolean',
        'accepted_banks' => 'array',
        'document_type_ids' => 'array',
        'estimated_duration_days' => 'integer',
        'sla_alert_days' => 'integer',
    ];

    public function processFlow(): BelongsTo
    {
        return $this->belongsTo(ProcessFlow::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProcessFlowSection::class, 'process_flow_section_id');
    }
}
