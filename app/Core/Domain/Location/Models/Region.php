<?php

namespace App\Core\Domain\Location\Models;

use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Location\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Region extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'regions';

    public array $translatable = ['name'];

    protected $fillable = ['name', 'slug', 'country_id'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}