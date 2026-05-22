<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ProcessFlowSection extends Model
{
    use HasTranslations;

    protected $table = 'process_flow_sections';

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'process_flow_id',
        'key',
        'title',
        'description',
        'section_order',
        'color',
        'icon',
        'visible_after_section_key',
    ];

    protected $casts = [
        'process_flow_id' => 'integer',
        'section_order' => 'integer',
    ];

    public function processFlow(): BelongsTo
    {
        return $this->belongsTo(ProcessFlow::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcessStep::class)->orderBy('step_order');
    }
}
