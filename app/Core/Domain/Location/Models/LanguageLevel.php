<?php

namespace App\Core\Domain\Location\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LanguageLevel extends Model
{
    use HasTranslations;

    protected $table = 'language_levels';

    public array $translatable = ['name'];

    protected $fillable = ['code', 'name', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
