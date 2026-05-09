<?php

namespace App\Core\Domain\Location\Models;

use App\Core\Domain\Location\Models\Region;
use App\Core\Domain\Catalog\Models\Offer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'cities';

    public array $translatable = ['name'];

    protected $fillable = ['name', 'slug', 'region_id', 'zip_code'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Relation avec les offres situées dans cette ville.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}