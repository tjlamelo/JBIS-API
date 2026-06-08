<?php

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditedModel;
use App\Core\Domain\Location\Models\Language as CatalogLanguage;
use App\Core\Domain\Location\Models\LanguageLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends AuditedModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_languages';

    protected $fillable = [
        'user_id',
        'language_id',
        'language_level_id',
        'is_approved',
        'approved_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'user_id' => 'integer',
        'language_id' => 'integer',
        'language_level_id' => 'integer',
        'approved_by' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(CatalogLanguage::class, 'language_id');
    }

    public function languageLevel(): BelongsTo
    {
        return $this->belongsTo(LanguageLevel::class, 'language_level_id');
    }
}
