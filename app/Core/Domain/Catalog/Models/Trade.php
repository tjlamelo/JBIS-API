<?php

namespace App\Core\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Trade extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['category_id', 'name', 'slug', 'is_active'];

    public array $translatable = ['name'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}
