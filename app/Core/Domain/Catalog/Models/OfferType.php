<?php

namespace App\Core\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class OfferType extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'offer_types';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Relation avec les offres
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'offer_type_id');
    }
}