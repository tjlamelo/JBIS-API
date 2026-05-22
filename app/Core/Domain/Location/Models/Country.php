<?php

namespace App\Core\Domain\Location\Models;

use App\Core\Domain\Identity\Models\UserPreferredCountry;
use App\Core\Domain\Identity\Models\UserVisaHistory;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'code', 'flag', 'phone_code', 'is_active'];

    public $translatable = ['name'];

    protected static function newFactory()
    {
        return CountryFactory::new();
    }

    public function userPreferredCountries(): HasMany
    {
        return $this->hasMany(UserPreferredCountry::class);
    }

    public function userVisaHistories(): HasMany
    {
        return $this->hasMany(UserVisaHistory::class);
    }
}
