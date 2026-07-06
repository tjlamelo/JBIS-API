<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function documentTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Core\Domain\Identity\Models\DocumentType::class,
            'process_step_document_type',
            'process_step_id',
            'document_type_id',
        )->withTimestamps();
    }

    /**
     * @return list<int>
     */
    public function resolvedDocumentTypeIds(): array
    {
        if ($this->relationLoaded('documentTypes') && $this->documentTypes->isNotEmpty()) {
            return $this->documentTypes->pluck('id')->map(static fn (mixed $id): int => (int) $id)->values()->all();
        }

        if ($this->documentTypes()->exists()) {
            return $this->documentTypes()->pluck('document_types.id')->map(static fn (mixed $id): int => (int) $id)->values()->all();
        }

        return is_array($this->document_type_ids) ? array_values(array_map('intval', $this->document_type_ids)) : [];
    }
}
