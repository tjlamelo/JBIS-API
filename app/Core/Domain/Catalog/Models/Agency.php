<?php

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Agency extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'agencies';

    // Champs traduisibles via Spatie
    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'country_id',
        'city_id',
        'address',
        'latitude',
        'longitude',
        'phones',
        'whatsapp_numbers',
        'email',
        'manager_id',
        'number_of_employees',
        'opening_hours',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'phones' => 'array',
        'whatsapp_numbers' => 'array',
        'opening_hours' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'number_of_employees' => 'integer',
    ];

    /**
     * Relation avec l'utilisateur Manager (FK)
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Pays de l'agence
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Ville de l'agence
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Profils utilisateurs rattachés à cette agence
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'agency_id');
    }
}
