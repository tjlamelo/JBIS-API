<?php

namespace App\Core\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // 1. Importation du trait
use Illuminate\Database\Eloquent\Model;
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
}