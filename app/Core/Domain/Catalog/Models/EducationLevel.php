<?php

namespace App\Core\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class EducationLevel extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'education_levels';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'education_level_id');
    }
}
