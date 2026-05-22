<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class LegalDocument extends Model
{
    use HasTranslations;

    protected $fillable = [
        'type',
        'version',
        'title',
        'content',
        'summary',
        'effective_at',
        'is_current',
        'requires_reacceptance',
        'published_by',
    ];

    public array $translatable = ['title', 'content'];

    protected $casts = [
        'effective_at' => 'datetime',
        'is_current' => 'boolean',
        'requires_reacceptance' => 'boolean',
        'published_by' => 'integer',
    ];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
