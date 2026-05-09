<?php

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Catalog\Models\Offer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Benefit extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'benefits';

    public array $translatable = ['name', 'slug'];

    protected $fillable = ['name', 'slug', 'icon'];

    /**
     * Relation Many-to-Many avec les offres.
     */
    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'benefit_offer');
    }
}