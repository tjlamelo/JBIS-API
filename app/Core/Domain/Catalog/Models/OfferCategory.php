<?php

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class OfferCategory extends Model
{
    use HasFactory, HasTranslations; // 2. Utilisation du trait

    protected $fillable = ['name', 'slug', 'icon', 'is_active'];

    public $translatable = ['name'];

    /**
     * 3. On force le lien vers la Factory car le namespace est personnalisé
     */
    protected static function newFactory()
    {
        return \Database\Factories\OfferCategoryFactory::new();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_sector', 'offer_category_id', 'user_id')
            ->withTimestamps();
    }
}